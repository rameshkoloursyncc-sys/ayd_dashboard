<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PharmaCompany;
use App\Models\User;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PharmaCompanyController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        try {
            $response = $this->pinktreeApiService->getAllPharma();

            if ($response->successful()) {
                $apiPharmaCompanies = $response->json();
                if (isset($apiPharmaCompanies['data']) && is_array($apiPharmaCompanies['data'])) {
                    $apiPharmaCompanies = $apiPharmaCompanies['data'];
                }

                $services = [];
                $serviceNameMap = [];
                try {
                    $servicesResponse = $this->pinktreeApiService->listServices();
                    if ($servicesResponse->successful()) {
                        $serviceData = $servicesResponse->json();
                        $services = $serviceData['data'] ?? $serviceData ?? [];
                        foreach ($services as $service) {
                            $serviceNameMap[$service['_id']] = $service['serviceName'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Exception fetching services for pharma companies index: ' . $e->getMessage());
                }

                $pharmaCompanies = collect($apiPharmaCompanies)->map(function ($company) use ($serviceNameMap) {
                    $company['pharmaCompanyName'] = $company['name'] ?? 'N/A';
                    $speciality = $company['specialitySignedUpFor'] ?? null;
                    if (is_array($speciality)) {
                        $names = array_map(function($sid) use ($serviceNameMap) {
                            return $serviceNameMap[$sid] ?? $sid;
                        }, $speciality);
                        $company['specialitySignedUpForName'] = implode(', ', $names);
                    } else {
                        $company['specialitySignedUpForName'] = $serviceNameMap[$speciality] ?? ($speciality ?? 'N/A');
                    }
                    $company['local_record'] = PharmaCompany::where('api_id', $company['_id'])->first();
                    return $company;
                });

            } else {
                Log::error('Pharma Companies Index - getAllPharma API Response (Failed):', ['body' => $response->body()]);
                $pharmaCompanies = collect();
                session()->flash('error', 'Could not retrieve pharma companies from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch pharma companies: ' . $e->getMessage());
            $pharmaCompanies = collect();
            session()->flash('error', 'Could not connect to the API to fetch pharma companies.');
        }
        return view('superadmin.pharma-companies.index', compact('pharmaCompanies'));
    }

    public function create()
    {
        $services = [];
        $banners = [];

        try {
            $servicesResponse = $this->pinktreeApiService->listServices();
            if ($servicesResponse->successful()) {
                $serviceData = $servicesResponse->json();
                $services = $serviceData['data'] ?? $serviceData ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch services from API: ' . $e->getMessage());
            session()->flash('error', 'Could not fetch services from the API. Please check the connection and try again.');
        }

        try {
            $bannersResponse = $this->pinktreeApiService->listAydBanner();
            if ($bannersResponse->successful()) {
                $bannerData = $bannersResponse->json();
                $banners = $bannerData['data'] ?? $bannerData ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch banners from API: ' . $e->getMessage());
            session()->flash('error', 'Could not fetch banners from the API. Please check the connection and try again.');
        }

        return view('superadmin.pharma-companies.create', compact('services', 'banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'pharma_co_code' => 'required',
            'name' => 'required',
            'speciality' => 'required|array',
            'speciality.*' => 'string',
            'campaign_start_date' => 'required|date',
            'campaign_end_date' => 'required|date',
        ]);

        $existingUser = User::where('email', $request->admin_email)->first();
        if ($existingUser) {
            return back()->withErrors(['admin_email' => 'A user with this email already exists.'])->withInput();
        }

        try {
            // 1. Create the user first
            $user = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'pharma_admin',
            ]);

            // 2. Create pharma company on external API
            $specialityPayload = is_array($request->speciality) ? array_values($request->speciality) : [$request->speciality];
            $response = $this->pinktreeApiService->createPharma([
                'pharmaCoCode' => $request->pharma_co_code,
                'name' => $request->name,
                'specialitySignedUpFor' => $specialityPayload,
                'campaignTimeStartPeriod' => $request->campaign_start_date,
                'campaignTimeEndPeriod' => $request->campaign_end_date,
                'digitalScratchCardConnectedTo' => $request->unique_code_pool,
                'advertisement' => $request->advertisement,
                'banner' => $request->banner,
                'totalActivationQuota' => (int)$request->total_activation_quota,
            ]);

            if ($response->successful()) {
                Log::info('Pharma Company Store - createPharma API Response (Success):', ['body' => $response->json()]);
            } else {
                Log::error('Pharma Company Store - createPharma API Response (Failed):', ['body' => $response->body()]);
                $user->delete();
                return back()->withErrors(['error' => 'Failed to create pharma company via API: ' . $response->body()])->withInput();
            }

            $apiPharmaCompany = $response->json();
            $pharmaData = $apiPharmaCompany['data'] ?? null;
            if (!$pharmaData || !isset($pharmaData['_id'])) {
                $user->delete();
                Log::error('Pharma company creation failed: API response missing data._id', ['api_response' => $apiPharmaCompany]);
                return back()->withErrors(['error' => 'Pharma company creation failed: API response missing ID.'])->withInput();
            }

            $localPharmaCompany = PharmaCompany::create([
                'api_id' => $pharmaData['_id'],
                'user_id' => $user->id,
            ]);

            $user->pharma_company_id = $localPharmaCompany->id;
            $user->save();

            return redirect()->route('superadmin.pharma-companies.index')
                             ->with('success', 'Pharma Company and Admin User created successfully.');

        } catch (\Exception $e) {
            Log::error('Pharma company creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred. Please check the logs.'])->withInput();
        }
    }
    
    public function show(string $pharmaCompanyApiId)
    {
        $response = $this->pinktreeApiService->getByPharmaId($pharmaCompanyApiId);

        if ($response->successful()) {
            $apiResponse = $response->json();
            Log::info('Pharma Company Show - getByPharmaId API Response (Success):', ['body' => $apiResponse]);
            $pharmaData = $apiResponse['data'] ?? null;
            if (empty($pharmaData)) {
                return redirect()->route('superadmin.pharma-companies.index')
                                 ->withErrors(['error' => 'Pharma Company data not found.']);
            }
            // Fetch all services and banners for mapping
            $servicesResponse = $this->pinktreeApiService->listServices();
            $bannersResponse = $this->pinktreeApiService->listAydBanner();
            $serviceMap = [];
            $bannerUrl = null;
            if ($servicesResponse->successful()) {
                $serviceData = $servicesResponse->json();
                $services = $serviceData['data'] ?? $serviceData ?? [];
                foreach ($services as $service) {
                    $serviceMap[$service['_id']] = $service['serviceName'] ?? $service['_id'];
                }
            }
            // Find banner image URL by searching all banners for matching ID and using 'image' field
            if ($bannersResponse->successful() && !empty($pharmaData['banner'])) {
                $bannerData = $bannersResponse->json();
                $banners = $bannerData['data'] ?? $bannerData ?? [];
                foreach ($banners as $banner) {
                    if (isset($banner['_id']) && $banner['_id'] == $pharmaData['banner']) {
                        $bannerUrl = $banner['image'] ?? null;
                        break;
                    }
                }
            }
            // Debug log for banner mapping
            Log::info('SuperAdmin PharmaCompanyController@show banner debug', [
                'pharmaData_banner' => $pharmaData['banner'] ?? null,
                'resolved_bannerUrl' => $bannerUrl,
            ]);
            // Map specialitySignedUpFor to name
            $specialityRaw = $pharmaData['specialitySignedUpFor'] ?? null;
            if (is_array($specialityRaw)) {
                $names = [];
                foreach ($specialityRaw as $sid) {
                    $names[] = $serviceMap[$sid] ?? $sid;
                }
                $specialityName = implode(', ', $names);
            } else {
                $specialityName = isset($specialityRaw) && isset($serviceMap[$specialityRaw])
                    ? $serviceMap[$specialityRaw]
                    : ($specialityRaw ?? 'N/A');
            }
            // Advertisement is not an image, just pass as value
            $advertisementValue = $pharmaData['advertisement'] ?? null;
            $localPharmaCompany = PharmaCompany::where('api_id', $pharmaCompanyApiId)->first();
            $adminUser = null;
            if ($localPharmaCompany) {
                $adminUser = User::where('pharma_company_id', $localPharmaCompany->id)->where('role', 'pharma_admin')->first();
            }
            return view('superadmin.pharma-companies.show', [
                'pharmaCompany' => $pharmaData,
                'adminUser' => $adminUser,
                'localPharmaCompany' => $localPharmaCompany, // Pass local record info
                'specialityName' => $specialityName,
                'bannerUrl' => $bannerUrl,
                'advertisementValue' => $advertisementValue,
            ]);
        }

        Log::error('Pharma Company Show - getByPharmaId API Response (Failed):', ['body' => $response->body()]);
        return redirect()->route('superadmin.pharma-companies.index')
                         ->withErrors(['error' => 'Pharma Company not found.']);
    }

    public function edit(string $pharmaCompanyApiId)
    {
        $services = [];
        $banners = [];
        $pharmaData = null;

        try {
            $response = $this->pinktreeApiService->getByPharmaId($pharmaCompanyApiId);
            if ($response->failed()) {
                Log::error('Pharma Company Edit - getByPharmaId API Response (Failed):', ['body' => $response->body()]);
                return redirect()->route('superadmin.pharma-companies.index')
                                 ->withErrors(['error' => 'Pharma Company not found or API error.']);
            }
            $apiResponse = $response->json();
            Log::info('Pharma Company Edit - getByPharmaId API Response (Success):', ['body' => $apiResponse]);
            $pharmaData = $apiResponse['data'] ?? null;
            if (empty($pharmaData) || !isset($pharmaData['_id'])) {
                return redirect()->route('superadmin.pharma-companies.index')
                                 ->withErrors(['error' => 'Pharma Company data not found for editing.']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch pharma company from API: ' . $e->getMessage());
            return redirect()->route('superadmin.pharma-companies.index')
                             ->withErrors(['error' => 'Could not fetch company details from the API.']);
        }

        try {
            $servicesResponse = $this->pinktreeApiService->listServices();
            if ($servicesResponse->successful()) {
                Log::info('Pharma Company Edit - listServices API Response (Success):', ['body' => $servicesResponse->json()]);
                $serviceData = $servicesResponse->json();
                $services = $serviceData['data'] ?? $serviceData ?? [];
            } else {
                Log::error('Pharma Company Edit - listServices API Response (Failed):', ['body' => $servicesResponse->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch services from API: ' . $e->getMessage());
            session()->flash('error', 'Could not fetch services from the API.');
        }

        try {
            $bannersResponse = $this->pinktreeApiService->listAydBanner();
            if ($bannersResponse->successful()) {
                $bannerData = $bannersResponse->json();
                $banners = $bannerData['data'] ?? $bannerData ?? [];
            } else {
                Log::error('Pharma Company Edit - listAydBanner API Response (Failed):', ['body' => $bannersResponse->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch banners from API: ' . $e->getMessage());
            session()->flash('error', 'Could not fetch banners from the API.');
        }

        $updatableFields = [
            '_id',
            'pharmaCoCode',
            'name',
            'specialitySignedUpFor',
            'campaignTimeStartPeriod',
            'campaignTimeEndPeriod',
            'digitalScratchCardConnectedTo',
            'advertisement',
            'banner',
            'totalActivationQuota',
        ];
        $pharmaCompanyForEdit = array_intersect_key($pharmaData, array_flip($updatableFields));

        // Normalize campaign dates to yyyy-MM-dd for HTML date inputs
        try {
            if (!empty($pharmaCompanyForEdit['campaignTimeStartPeriod'])) {
                $start = $pharmaCompanyForEdit['campaignTimeStartPeriod'];
                $dt = new \DateTime($start);
                $pharmaCompanyForEdit['campaignTimeStartPeriod'] = $dt->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Leave as-is if parsing fails
        }

        try {
            if (!empty($pharmaCompanyForEdit['campaignTimeEndPeriod'])) {
                $end = $pharmaCompanyForEdit['campaignTimeEndPeriod'];
                $dt2 = new \DateTime($end);
                $pharmaCompanyForEdit['campaignTimeEndPeriod'] = $dt2->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Leave as-is if parsing fails
        }

        return view('superadmin.pharma-companies.edit', [
            'pharmaCompany' => $pharmaCompanyForEdit,
            'services' => $services,
            'banners' => $banners,
            'localPharmaCompany' => PharmaCompany::where('api_id', $pharmaCompanyApiId)->first(), // Pass local record info
        ]);
    }

    public function update(Request $request, string $pharmaCompanyApiId)
    {
        $request->validate([
            'pharma_co_code' => 'required',
            'name' => 'required',
            'speciality' => 'required|array',
            'speciality.*' => 'string',
            'campaign_start_date' => 'required|date',
            'campaign_end_date' => 'required|date',
            'unique_code_pool' => 'nullable',
            'advertisement' => 'nullable',
            'banner' => 'nullable',
        ]);

        try {
            $specialityPayload = is_array($request->speciality) ? array_values($request->speciality) : [$request->speciality];
            $response = $this->pinktreeApiService->createPharma([ // Pinktree API uses createPharma for update if _id is present
                '_id' => $pharmaCompanyApiId,
                'pharmaCoCode' => $request->pharma_co_code,
                'name' => $request->name,
                'specialitySignedUpFor' => $specialityPayload,
                'campaignTimeStartPeriod' => $request->campaign_start_date,
                'campaignTimeEndPeriod' => $request->campaign_end_date,
                'digitalScratchCardConnectedTo' => $request->unique_code_pool,
                'advertisement' => $request->advertisement,
                'banner' => $request->banner,
                'totalActivationQuota' => (int)$request->total_activation_quota,
            ]);

            if ($response->successful()) {
                Log::info('Pharma Company Update - createPharma API Response (Success):', ['body' => $response->json()]);

                // Update local record if it exists
                $localPharmaCompany = PharmaCompany::where('api_id', $pharmaCompanyApiId)->first();
                if ($localPharmaCompany) {
                    $localPharmaCompany->update([
                        'api_id' => $pharmaCompanyApiId, // Ensure api_id is correct
                    ]);

                    // Update the associated user's details if a Pharma Admin is linked
                    $adminUser = User::where('pharma_company_id', $localPharmaCompany->id)->where('role', 'pharma_admin')->first();
                    if ($adminUser) {
                        $adminUser->update([
                            'name' => $request->name, // Assuming the name update reflects in the admin name
                            // More fields can be updated here if needed
                        ]);
                    }
                }

                return redirect()->route('superadmin.pharma-companies.index')
                                 ->with('success', 'Pharma Company updated successfully.');
            } else {
                Log::error('Pharma Company Update - createPharma API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update pharma company via API: ' . $response->body()])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Pharma company update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy(string $pharmaCompanyApiId)
    {
        try {
            $response = $this->pinktreeApiService->deletePharma($pharmaCompanyApiId);
            if ($response->successful()) {
                Log::info('Pharma Company Destroy - deletePharma API Response (Success): Pharma Company ' . $pharmaCompanyApiId . ' deleted.');
                
                // Delete local record and associated user if they exist
                $localPharmaCompany = PharmaCompany::where('api_id', $pharmaCompanyApiId)->first();
                if ($localPharmaCompany) {
                    $adminUser = User::where('pharma_company_id', $localPharmaCompany->id)->where('role', 'pharma_admin')->first();
                    $localPharmaCompany->delete();
                    if ($adminUser) {
                        $adminUser->delete();
                    }
                }

                return redirect()->route('superadmin.pharma-companies.index')
                                 ->with('success', 'Pharma Company deleted successfully.');
            } else {
                Log::error('Pharma Company Destroy - deletePharma API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete pharma company.']);
            }
        } catch (\Exception $e) {
            Log::error('Pharma company deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }

    public function linkLocal(string $pharmaCompanyApiId)
    {
        try {
            // Fetch company details from Pinktree API
            $response = $this->pinktreeApiService->getByPharmaId($pharmaCompanyApiId);
            if ($response->failed() || !isset($response->json()['data'])) {
                return back()->withErrors(['error' => 'Could not fetch Pharma Company details from API to link.'])->withInput();
            }
            $pharmaData = $response->json()['data'];

            // Validate necessary fields from API response
            if (empty($pharmaData['name']) || empty($pharmaData['_id'])) {
                return back()->withErrors(['error' => 'Missing required data from API to link local record.'])->withInput();
            }

            // Check if a local record already exists for this api_id
            $existingLocal = PharmaCompany::where('api_id', $pharmaCompanyApiId)->first();
            if ($existingLocal) {
                return back()->withErrors(['error' => 'A local record for this Pharma Company already exists.'])->withInput();
            }

            // Create a placeholder user for this pharma company (to be updated by Super Admin later)
            // Or, redirect to a form to create a Pharma Admin user
            // For simplicity, let's create a dummy user for now.
            $adminUser = User::create([
                'name' => $pharmaData['name'] . ' Admin',
                'email' => 'admin_' . $pharmaData['_id'] . '@example.com', // Unique dummy email
                'password' => Hash::make('password'), // Temporary password
                'role' => 'pharma_admin',
            ]);

            // Create local PharmaCompany record
            $localPharmaCompany = PharmaCompany::create([
                'api_id' => $pharmaData['_id'],
                'user_id' => $adminUser->id,
            ]);

            // Update the dummy user with the actual pharma_company_id
            $adminUser->pharma_company_id = $localPharmaCompany->id;
            $adminUser->save();

            return redirect()->route('superadmin.pharma-companies.index')->with('success', 'Pharma Company linked locally and admin user created.');

        } catch (\Exception $e) {
            Log::error('Failed to link local Pharma Company: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred while linking local Pharma Company.'])->withInput();
        }
    }
}