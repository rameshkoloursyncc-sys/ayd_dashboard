<?php

namespace App\Http\Controllers\PharmaAdmin;

use App\Http\Controllers\Controller;
use App\Models\PharmaCompany;
use App\Models\User;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MyCompanyController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function show()
    {
        $user = Auth::user();
        $localPharmaCompany = PharmaCompany::where('id', $user->pharma_company_id)->first();

        if (!$localPharmaCompany) {
            return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Your company record not found.']);
        }

        try {
            $response = $this->pinktreeApiService->getByPharmaId($localPharmaCompany->api_id);

            if ($response->successful()) {
                $apiResponse = $response->json();
                Log::info('My Company Show - getByPharmaId API Response (Success):', ['body' => $apiResponse]);
                $pharmaData = $apiResponse['data'] ?? null;
                if (empty($pharmaData)) {
                    return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Company data not found from API.']);
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
                // Map specialitySignedUpFor to name (handle array, JSON string, comma-separated, or single)
                $specialityRaw = $pharmaData['specialitySignedUpFor'] ?? null;
                $resolved = [];
                if (is_array($specialityRaw)) {
                    $ids = $specialityRaw;
                } elseif (is_string($specialityRaw)) {
                    $decoded = json_decode($specialityRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $ids = $decoded;
                    } elseif (strpos($specialityRaw, ',') !== false) {
                        $parts = array_map('trim', explode(',', $specialityRaw));
                        $ids = array_map(function($p) { return trim($p, "\'\" "); }, $parts);
                    } else {
                        $ids = [$specialityRaw];
                    }
                } else {
                    $ids = [];
                }

                foreach ($ids as $sid) {
                    if (isset($serviceMap[$sid])) {
                        $resolved[] = $serviceMap[$sid];
                    } elseif (!empty($sid)) {
                        $resolved[] = $sid;
                    }
                }
                $specialityName = !empty($resolved) ? implode(', ', $resolved) : 'N/A';
                // Advertisement is not an image, just pass as value
                $advertisementValue = $pharmaData['advertisement'] ?? null;
                return view('pharma-admin.my-company.show', [
                    'pharmaCompany' => $pharmaData,
                    'localPharmaCompany' => $localPharmaCompany,
                    'specialityName' => $specialityName,
                    'bannerUrl' => $bannerUrl,
                    'advertisementValue' => $advertisementValue,
                ]);
            }

            Log::error('My Company Show - getByPharmaId API Response (Failed):', ['body' => $response->body()]);
            return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Could not retrieve company details from API.']);
        } catch (\Exception $e) {
            Log::error('Failed to fetch my company details: ' . $e->getMessage());
            return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function edit()
    {
        $user = Auth::user();
        $localPharmaCompany = PharmaCompany::where('id', $user->pharma_company_id)->first();

        if (!$localPharmaCompany) {
            return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Your company record not found.']);
        }

        $services = [];
        $banners = [];
        $pharmaData = null;

        try {
            $response = $this->pinktreeApiService->getByPharmaId($localPharmaCompany->api_id);
            if ($response->failed()) {
                Log::error('My Company Edit - getByPharmaId API Response (Failed):', ['body' => $response->body()]);
                return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Company data not found or API error.']);
            }
            $apiResponse = $response->json();
            Log::info('My Company Edit - getByPharmaId API Response (Success):', ['body' => $apiResponse]);
            $pharmaData = $apiResponse['data'] ?? null;
            if (empty($pharmaData) || !isset($pharmaData['_id'])) {
                return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Company data not found for editing.']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch my company details from API: ' . $e->getMessage());
            return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Could not fetch company details from the API.']);
        }

        try {
            $servicesResponse = $this->pinktreeApiService->listServices();
            if ($servicesResponse->successful()) {
                $serviceData = $servicesResponse->json();
                $services = $serviceData['data'] ?? $serviceData ?? [];
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

        // Normalize specialitySignedUpFor to an array for the edit form
        $rawSpeciality = $pharmaCompanyForEdit['specialitySignedUpFor'] ?? null;
        if (is_array($rawSpeciality)) {
            // ok
        } elseif (is_string($rawSpeciality)) {
            $decoded = json_decode($rawSpeciality, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $pharmaCompanyForEdit['specialitySignedUpFor'] = $decoded;
            } elseif (strpos($rawSpeciality, ',') !== false) {
                $parts = array_map('trim', explode(',', $rawSpeciality));
                $pharmaCompanyForEdit['specialitySignedUpFor'] = array_map(function($p){ return trim($p, "\'\" "); }, $parts);
            } else {
                $pharmaCompanyForEdit['specialitySignedUpFor'] = $rawSpeciality ? [$rawSpeciality] : [];
            }
        } else {
            $pharmaCompanyForEdit['specialitySignedUpFor'] = [];
        }

        return view('pharma-admin.my-company.edit', [
            'pharmaCompany' => (object)$pharmaCompanyForEdit,
            'services' => $services,
            'banners' => $banners,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $localPharmaCompany = PharmaCompany::where('id', $user->pharma_company_id)->first();

        if (!$localPharmaCompany) {
            return redirect()->route('pharma-admin.dashboard')->withErrors(['error' => 'Your company record not found.']);
        }

        $request->validate([
            'pharmaCoCode' => 'required',
            'name' => 'required',
            'speciality' => 'required',
            'campaignTimeStartPeriod' => 'required|date',
            'campaignTimeEndPeriod' => 'required|date',
            'digitalScratchCardConnectedTo' => 'nullable',
            'advertisement' => 'nullable',
            'banner' => 'nullable',
            'totalActivationQuota' => 'required|integer',
        ]);

        try {
            $response = $this->pinktreeApiService->createPharma([
                '_id' => $localPharmaCompany->api_id,
                'pharmaCoCode' => $request->pharmaCoCode,
                'name' => $request->name,
                'specialitySignedUpFor' => $request->speciality,
                'campaignTimeStartPeriod' => $request->campaignTimeStartPeriod,
                'campaignTimeEndPeriod' => $request->campaignTimeEndPeriod,
                'digitalScratchCardConnectedTo' => $request->digitalScratchCardConnectedTo,
                'advertisement' => $request->advertisement,
                'banner' => $request->banner,
                'totalActivationQuota' => (int)$request->totalActivationQuota,
            ]);

            if ($response->successful()) {
                Log::info('My Company Update - createPharma API Response (Success):', ['body' => $response->json()]);
                // Only local user name needs update if it reflects company name
                $user->name = $request->name;
                $user->save();

                return redirect()->route('pharma-admin.my-company.show')
                                 ->with('success', 'Company details updated successfully.');
            } else {
                Log::error('My Company Update - createPharma API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update company details via API: ' . $response->body()])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('My Company update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }
}