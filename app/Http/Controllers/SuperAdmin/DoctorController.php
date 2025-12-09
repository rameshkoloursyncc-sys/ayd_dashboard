<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Services\DoctorCreationService;
use App\Models\PharmaCompany;
use App\Models\MedicalExecutive;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    // ...existing code...

    /**
     * Attach a doctor to a pharma company (Super Admin action)
     */
    public function attachPharma(Request $request, $api_id)
    {
        $request->validate([
            'pharma_company_id' => 'required|exists:pharma_companies,api_id',
        ]);

        // Find the pharma company by API ID
        $pharmaCompany = \App\Models\PharmaCompany::where('api_id', $request->pharma_company_id)->first();
        if (!$pharmaCompany) {
            return back()->withErrors(['pharma_company_id' => 'Pharma company not found.']);
        }

        // Enforce Pinktree totalActivationQuota at runtime (do not persist quota locally)
        try {
            $pharmaDetailResp = $this->pinktreeApiService->getByPharmaId($request->pharma_company_id);
            if ($pharmaDetailResp->successful()) {
                $pharmaData = $pharmaDetailResp->json('data') ?? [];
                $quota = isset($pharmaData['totalActivationQuota']) ? intval($pharmaData['totalActivationQuota']) : null;
                if (!is_null($quota) && $quota > 0) {
                    $currentCount = \App\Models\Doctor::where('pharma_company_id', $pharmaCompany->id)->count();
                    // If attaching a doctor that is not yet local, this attach will increase count by 1
                    $isLocal = \App\Models\Doctor::where('api_id', $api_id)->exists();
                    $projected = $currentCount + ($isLocal ? 0 : 1);
                    if ($projected > $quota) {
                        return back()->withErrors(['quota' => "Cannot attach doctor: pharma activation quota ({$quota}) reached."]);
                    }
                }
            }
        } catch (\Exception $e) {
            // If the Pinktree API fails, do NOT proceed — return a clear error message (fail-closed).
            Log::warning('Failed to fetch pharma details for quota check: ' . $e->getMessage());
            return back()->withErrors(['quota_check' => 'Unable to verify pharma activation quota at this time. Please try again later.']);
        }

        // Find or create the local doctor mapping
        $doctor = \App\Models\Doctor::where('api_id', $api_id)->first();
        if (!$doctor) {
            // Fetch doctor info from remote API
            $apiService = app(\App\Services\PinktreeApiService::class);
            $response = $apiService->getCDoctorInfo($api_id);
            $apiData = $response->successful() ? $response->json('data') : [];
            $doctor = new \App\Models\Doctor();
            $doctor->api_id = $api_id;
            $doctor->pharma_company_id = $pharmaCompany->id;
            // Optionally set other fields if needed (e.g., mobile_no)
            $doctor->save();
        } else {
            $doctor->pharma_company_id = $pharmaCompany->id;
            $doctor->save();
        }

        // If a medical_executive_id was provided (could be remote API _id or local id), try to map to local ID
        if ($request->filled('medical_executive_id')) {
            $meInput = $request->input('medical_executive_id');
            $localExec = null;
            // If numeric, assume local ID
            if (is_numeric($meInput)) {
                $localExec = \App\Models\MedicalExecutive::where('id', intval($meInput))->first();
            }
            // If not found or not numeric, try matching by api_id
            if (!$localExec) {
                $localExec = \App\Models\MedicalExecutive::where('api_id', $meInput)->first();
            }
            if ($localExec) {
                $doctor->medical_executive_id = $localExec->id;
                $doctor->save();
            } else {
                // If mapping not found, attempt to auto-create a local MedicalExecutive and User
                Log::info('attachPharma: provided medical_executive_id could not be mapped to a local record, attempting auto-create', ['input' => $meInput, 'doctor_api_id' => $api_id]);
                try {
                    // Try to fetch exec details from Pinktree by listing practitioners for the pharma
                    $pharmaApiId = $pharmaCompany->api_id;
                    $resp = $this->pinktreeApiService->getMedicalPractitionerByPharma($pharmaApiId);
                    $execData = null;
                    if ($resp->successful()) {
                        $list = $resp->json('data') ?? [];
                        foreach ($list as $item) {
                            if (isset($item['_id']) && $item['_id'] == $meInput) {
                                $execData = $item;
                                break;
                            }
                        }
                    }

                    // Build sensible defaults for local user
                    $userName = $execData['name'] ?? ('ME ' . substr($meInput, 0, 8));
                    $userEmail = null;
                    if (!empty($execData['email'])) {
                        $userEmail = $execData['email'];
                    } else {
                        // generate unique placeholder email
                        $base = 'me_' . preg_replace('/[^a-z0-9]/i', '', $meInput);
                        $userEmail = $base . '@local.ayd';
                        // ensure uniqueness
                        $suffix = 0;
                        while (\App\Models\User::where('email', $userEmail)->exists()) {
                            $suffix++;
                            $userEmail = $base . '+' . $suffix . '@local.ayd';
                        }
                    }

                    $randomPassword = Str::random(12);
                    $user = \App\Models\User::create([
                        'name' => $userName,
                        'email' => $userEmail,
                        'password' => $randomPassword,
                        'role' => 'medical_executive',
                        'mobile_no' => $execData['phone'] ?? $execData['mobileNo'] ?? null,
                        'pharma_company_id' => $pharmaCompany->id,
                    ]);

                    $newExec = \App\Models\MedicalExecutive::create([
                        'pharma_company_id' => $pharmaCompany->id,
                        'user_id' => $user->id,
                        'api_id' => $meInput,
                    ]);

                    // Assign to doctor
                    $doctor->medical_executive_id = $newExec->id;
                    $doctor->save();

                    Log::info('attachPharma: auto-created local MedicalExecutive and User', ['medical_executive_id' => $newExec->id, 'user_id' => $user->id]);
                } catch (\Exception $ex) {
                    Log::error('attachPharma: failed to auto-create local MedicalExecutive', ['error' => $ex->getMessage(), 'input' => $meInput]);
                }
            }
        }

        return back()->with('success', 'Doctor attached to pharma company successfully.');
    }
    protected $creationService;
    protected $pinktreeApiService;

    public function __construct(DoctorCreationService $creationService, \App\Services\PinktreeApiService $pinktreeApiService)
    {
        $this->creationService = $creationService;
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        // Fetch all doctors from remote API (assume endpoint: /api/getAllCDoctors)
        $remoteDoctorsResponse = \Http::get(env('PINKTREE_API_BASE_URL') . '/api/getAllCDoctors');
        $remoteDoctors = $remoteDoctorsResponse->successful() ? ($remoteDoctorsResponse->json('data') ?? []) : [];

        // Fetch all local doctors
        $localDoctors = Doctor::with(['pharmaCompany', 'medicalExecutive.user'])->get()->keyBy('api_id');

        $doctors = collect($remoteDoctors)->map(function ($remoteDoctor) use ($localDoctors) {
            $apiId = $remoteDoctor['_id'] ?? null;
            $local = $apiId && $localDoctors->has($apiId) ? $localDoctors[$apiId] : null;

            $pharmaCompanyName = 'Not Mapped';
            $medicalExecutiveName = 'Not Mapped';
            if ($local) {
                if ($local->pharmaCompany) {
                    $pharmaResponse = app(\App\Services\PinktreeApiService::class)->getByPharmaId($local->pharmaCompany->api_id);
                    if ($pharmaResponse->successful() && isset($pharmaResponse->json('data')['name'])) {
                        $pharmaCompanyName = $pharmaResponse->json('data')['name'];
                    }
                }
                if ($local->medicalExecutive && $local->medicalExecutive->user) {
                    $medicalExecutiveName = $local->medicalExecutive->user->name;
                }
            }

            return (object) [
                'name' => $remoteDoctor['name'] ?? 'N/A',
                'email' => $remoteDoctor['email'] ?? 'N/A',
                'pharmaCompanyName' => $pharmaCompanyName,
                'medicalExecutiveName' => $medicalExecutiveName,
                'api_id' => $apiId,
                'is_local' => (bool) $local,
                'approvalStatus' => $remoteDoctor['approvalStatus'] ?? ($local && isset($local->approvalStatus) ? $local->approvalStatus : null),
            ];
        });

        return view('superadmin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $viewData = [];
        try {
            // Fetch pharma companies from Pinktree API
            $pharmaResponse = app(\App\Services\PinktreeApiService::class)->getAllPharma();
            $viewData['pharmaCompanies'] = $pharmaResponse->successful() ? collect($pharmaResponse->json('data'))->map(function($pharma) {
                return [
                    'id' => $pharma['_id'] ?? null,
                    'name' => $pharma['name'] ?? '',
                ];
            })->all() : [];

            $servicesResponse = app(\App\Services\PinktreeApiService::class)->listServices();
            $viewData['services'] = $servicesResponse->successful() ? collect($servicesResponse->json('data'))->map(function($service) {
                return [
                    'id' => $service['_id'] ?? null,
                    'name' => $service['serviceName'] ?? '',
                ];
            })->all() : [];

            \Log::info('[SuperAdmin][DoctorController@create] Data passed to view (API)', [
                'pharmaCompanies' => $viewData['pharmaCompanies'],
                'services' => $viewData['services'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch data for Super Admin Doctor creation: ' . $e->getMessage());
            $viewData['pharmaCompanies'] = [];
            $viewData['services'] = [];
            session()->flash('error', 'Could not fetch necessary data for doctor creation.');
        }
        return view('doctors.create', $viewData);
    }

    public function store(StoreDoctorRequest $request)
    {
        \Log::info('[SuperAdmin][DoctorController@store] ENTERED METHOD');
        $validatedData = $request->validated();
        Log::info('[SuperAdmin][DoctorController@store] Data after validation', $validatedData);

        try {
            $this->creationService->create($validatedData);
            Log::info('[SuperAdmin][DoctorController@store] Doctor creation succeeded');
            return redirect()->route('superadmin.doctors.index')->with('success', 'Doctor created successfully.');
        } catch (\Exception $e) {
            Log::error('[SuperAdmin][DoctorController@store] Doctor creation failed', [
                'error' => $e->getMessage(),
                'input' => $validatedData
            ]);
            return back()->withErrors(['error' => 'Failed to create Doctor: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Doctor $doctor)
    {
        $response = $this->pinktreeApiService->getCDoctorInfo($doctor->api_id);
        $apiData = $response->successful() ? $response->json('data') : [];

        // Map all expected fields from API to $doctor
        $fields = [
            'name', 'email', 'phone', 'gender', 'dob', 'age', 'degree', 'uniqueId', 'experience', 'placeName',
            'registrationNo', 'yearOfRegistration', 'recommendation', 'approvalStatus', 'createdAt', 'updatedAt',
        ];
        foreach ($fields as $field) {
            $doctor->{$field} = $apiData[$field] ?? null;
        }

        // Pharma company name
        $pharmaCompanyName = 'N/A';
        if ($doctor->pharmaCompany) {
            $pharmaResponse = $this->pinktreeApiService->getByPharmaId($doctor->pharmaCompany->api_id);
            if ($pharmaResponse->successful() && isset($pharmaResponse->json('data')['name'])) {
                $pharmaCompanyName = $pharmaResponse->json('data')['name'];
            }
        }
        $doctor->pharmaCompanyName = $pharmaCompanyName;

        // Resolve service names from service_ids
        $serviceNames = [];
        if (!empty($apiData['service_ids']) && is_array($apiData['service_ids'])) {
            $servicesResponse = $this->pinktreeApiService->listServices();
            $allServices = $servicesResponse->successful() ? $servicesResponse->json('data') : [];
            $serviceMap = [];
            foreach ($allServices as $service) {
                if (isset($service['_id']) && isset($service['serviceName'])) {
                    $serviceMap[$service['_id']] = $service['serviceName'];
                }
            }
            foreach ($apiData['service_ids'] as $sid) {
                if (isset($serviceMap[$sid])) {
                    $serviceNames[] = $serviceMap[$sid];
                }
            }
        }
        $doctor->service_names = $serviceNames;

        // Resolve medical executive name using local database relationship
        $doctor->medicalExecutiveName = 'N/A';
        if ($doctor->medical_executive_id) {
            $localMedicalExecutive = $doctor->medicalExecutive;
            if ($localMedicalExecutive && $localMedicalExecutive->user) {
                $doctor->medicalExecutiveName = $localMedicalExecutive->user->name;
            }
        }

        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor)
    {
        $response = $this->pinktreeApiService->getCDoctorInfo($doctor->api_id);
        $apiData = $response->successful() ? $response->json('data') : [];

        // Map all expected fields from API to $doctor
        $fields = [
            'name', 'email', 'phone', 'gender', 'dob', 'age', 'degree', 'uniqueId', 'experience', 'placeName',
            'registrationNo', 'yearOfRegistration', 'recommendation', 'approvalStatus', 'createdAt', 'updatedAt',
        ];
        foreach ($fields as $field) {
            $doctor->{$field} = $apiData[$field] ?? null;
        }

        // Set service_ids for multi-select
        $doctor->service_ids = isset($apiData['service_ids']) && is_array($apiData['service_ids']) ? $apiData['service_ids'] : [];

        // Fetch all services for the multi-select
        $servicesResponse = $this->pinktreeApiService->listServices();
        $services = $servicesResponse->successful() ? collect($servicesResponse->json('data'))->map(function($service) {
            return [
                'id' => $service['_id'] ?? null,
                'name' => $service['serviceName'] ?? '',
            ];
        })->all() : [];

        return view('doctors.edit', compact('doctor', 'services'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        // If approvalStatus update is being requested (e.g., approve doctor), allow that
        if ($request->has('approvalStatus')) {
            $validated = $request->validate([
                'approvalStatus' => 'required|string',
            ]);

            $response = $this->pinktreeApiService->updateDoctor($doctor->api_id, ['approvalStatus' => $validated['approvalStatus']]);

            if ($response->failed()) {
                Log::error('Doctor ApprovalStatus Update - updateDoctor API Response (Failed):', ['body' => $response->body()]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Failed to update Doctor approvalStatus via API.'], 500);
                }
                return back()->withErrors(['error' => 'Failed to update Doctor approvalStatus via API.'])->withInput();
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Doctor approval status updated successfully.']);
            }

            return redirect()->route('superadmin.doctors.index')->with('success', 'Doctor approval status updated successfully.');
        }

        // Fallback: update general doctor fields (name/email)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);

        $response = $this->pinktreeApiService->updateDoctor($doctor->api_id, $validatedData);

        if ($response->failed()) {
            Log::error('Doctor Update - updateDoctor API Response (Failed):', ['body' => $response->body()]);
            return back()->withErrors(['error' => 'Failed to update Doctor via API.'])->withInput();
        }

        return redirect()->route('superadmin.doctors.index')->with('success', 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor)
    {
        $response = $this->pinktreeApiService->deleteDoctor($doctor->api_id);

        if ($response->failed()) {
            Log::error('Doctor Destroy - deleteDoctor API Response (Failed):', ['body' => $response->body()]);
            return back()->withErrors(['error' => 'Failed to delete Doctor via API.'])->withInput();
        }

        $doctor->delete();

        return redirect()->route('superadmin.doctors.index')->with('success', 'Doctor deleted successfully.');
    }
}