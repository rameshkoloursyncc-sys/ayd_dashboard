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
        $api_id = trim($api_id);
        $request->validate([
            'pharma_company_id' => 'required|exists:pharma_companies,api_id',
            'amount' => 'nullable|numeric',
            'years' => 'nullable|integer|min:1',
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

        // Increment Quota
        if ($pharmaCompany->api_id) {
            $this->pinktreeApiService->incrementUsedActivationQuota($pharmaCompany->api_id, 1);
        }

        // Handle Subscription
        if ($request->has('subscribe_plan') && $request->subscribe_plan) {
            try {
                $subData = [
                    'pharmaId' => $pharmaCompany->api_id,
                    'doctorId' => $api_id,
                    'amount' => $request->amount ?? 1179,
                    'years' => $request->years ?? 1,
                    'planId' => null,
                ];
                $this->pinktreeApiService->subscribePlan($subData);
                Log::info('attachPharma: Subscribed doctor to plan', $subData);
            } catch (\Exception $e) {
                Log::error('attachPharma: Failed to subscribe doctor', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Doctor attached to pharma company successfully.');
    }

    public function subscribe(Request $request, $api_id)
    {
        $request->validate([
            // 'amount' => 'required|numeric', // Fixed internally
            // 'years' => 'required|integer|min:1', // Fixed internally
            'subscription_id' => 'nullable|string',
        ]);

        $doctor = \App\Models\Doctor::where('api_id', $api_id)->firstOrFail();

        if (!$doctor->pharmaCompany) {
            return back()->withErrors(['error' => 'Doctor must be attached to a pharma company before subscribing.']);
        }

        try {
            $subData = [
                'pharmaId' => $doctor->pharmaCompany->api_id,
                'doctorId' => $doctor->api_id,
                'amount' => 1179, // Fixed: 999 + 18% GST
                'years' => 1,     // Fixed: 1 Year
                'planId' => null,
            ];

            if ($request->filled('subscription_id')) {
                $subData['_id'] = $request->subscription_id;
            }

            $this->pinktreeApiService->subscribePlan($subData);
            Log::info('subscribe: Subscribed/Updated doctor plan', $subData);
            return back()->with('success', 'Subscription assigned/updated successfully.');
        } catch (\Exception $e) {
            Log::error('subscribe: Failed to subscribe doctor', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to assign subscription.']);
        }
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
        try {
            $remoteDoctorsResponse = \Http::get(env('PINKTREE_API_BASE_URL') . '/api/getAllCDoctors');
            $remoteDoctors = $remoteDoctorsResponse->successful() ? ($remoteDoctorsResponse->json('data') ?? []) : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch doctors from remote API: ' . $e->getMessage());
            $remoteDoctors = [];
            session()->flash('error', 'Unable to connect to the external API. Doctor list may be empty.');
        }

        // Fetch all local doctors
        $localDoctors = Doctor::with(['pharmaCompany', 'medicalExecutive.user'])->get()->keyBy('api_id');
        
        // Fetch local Pharma Companies for the dropdown
        $localPharmaCompanies = \App\Models\PharmaCompany::with('user')->get();

        Log::info('Local Doctors Keys:', $localDoctors->keys()->toArray());

        $doctors = collect($remoteDoctors)->map(function ($remoteDoctor) use ($localDoctors) {
            $apiId = isset($remoteDoctor['_id']) ? trim($remoteDoctor['_id']) : null;
            
            // Debug logging for specific check
            // Log::info("Checking remote ID: '$apiId' against local keys.");

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

        return view('superadmin.doctors.index', compact('doctors', 'localPharmaCompanies'));
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

            // Also pass local pharma companies (DB) so Super Admin can select local records
            $localPharmas = \App\Models\PharmaCompany::with('user')->get();
            $viewData['localPharmaCompanies'] = $localPharmas->map(function($p) {
                return ['id' => $p->id, 'name' => $p->user->name ?? $p->api_id ?? 'Pharma'];
            })->all();

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

    public function show($api_id)
    {
        // Try to find local doctor, but don't fail if not found
        $doctor = Doctor::where('api_id', $api_id)->first();
        
        // If not local, create a temporary instance
        if (!$doctor) {
            $doctor = new Doctor();
            $doctor->api_id = $api_id;
        }

        $response = $this->pinktreeApiService->getCDoctorInfo($api_id);
        if (!$response->successful()) {
            // Log status and body so we can debug missing fields
            Log::error('getCDoctorInfo failed for doctor: ' . $api_id, [
                'status' => method_exists($response, 'status') ? $response->status() : null,
                'body' => method_exists($response, 'body') ? $response->body() : null,
            ]);
        } else {
            Log::info('getCDoctorInfo successful for doctor: ' . $api_id);
        }

        $apiData = $response->successful() ? $response->json('data') : [];

        // Normalize API keys to our expected field names (maps alternate keys like emailId -> email)
        $normalized = $this->normalizeDoctorApiData(is_array($apiData) ? $apiData : []);

        // Map normalized fields onto the $doctor object
        $fields = [
            'name', 'email', 'phone', 'gender', 'dob', 'age', 'degree', 'uniqueId', 'experience', 'placeName',
            'registrationNo', 'yearOfRegistration', 'recommendation', 'approvalStatus', 'createdAt', 'updatedAt',
        ];
        foreach ($fields as $field) {
            $doctor->{$field} = $normalized[$field] ?? null;
        }

        // Pharma company name
        $pharmaCompanyName = 'N/A';
        if ($doctor->exists && $doctor->pharmaCompany) {
            $pharmaResponse = $this->pinktreeApiService->getByPharmaId($doctor->pharmaCompany->api_id);
            if ($pharmaResponse->successful() && isset($pharmaResponse->json('data')['name'])) {
                $pharmaCompanyName = $pharmaResponse->json('data')['name'];
            }
        }
        $doctor->pharmaCompanyName = $pharmaCompanyName;
        $doctor->pharmaCompanyName = $pharmaCompanyName;

        // Resolve service names from service_ids
        $serviceNames = [];
        if (!empty($normalized['service_ids']) && is_array($normalized['service_ids'])) {
            $servicesResponse = $this->pinktreeApiService->listServices();
            $allServices = $servicesResponse->successful() ? $servicesResponse->json('data') : [];
            $serviceMap = [];
            foreach ($allServices as $service) {
                if (isset($service['_id']) && isset($service['serviceName'])) {
                    $serviceMap[$service['_id']] = $service['serviceName'];
                }
            }
            foreach ($normalized['service_ids'] as $sid) {
                if (isset($serviceMap[$sid])) {
                    $serviceNames[] = $serviceMap[$sid];
                }
            }
        }
        $doctor->service_names = $serviceNames;

        // Resolve medical executive name using local database relationship
        $doctor->medicalExecutiveName = 'N/A';
        if ($doctor->exists && $doctor->medical_executive_id) {
            $localMedicalExecutive = $doctor->medicalExecutive;
            if ($localMedicalExecutive && $localMedicalExecutive->user) {
                $doctor->medicalExecutiveName = $localMedicalExecutive->user->name;
            }
        }

        // Check Subscription Plan
        $planDetails = null;
        try {
            $planResponse = $this->pinktreeApiService->checkDoctorPlan($api_id);
            if ($planResponse->successful()) {
                $planDetails = $planResponse->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to check doctor plan: ' . $e->getMessage());
        }

        return view('superadmin.doctors.show', compact('doctor', 'planDetails', 'apiData'));
    }

    public function edit($api_id)
    {
        // Try to find local doctor, but don't fail if not found
        $doctor = Doctor::where('api_id', $api_id)->first();
        
        // If not local, create a temporary instance
        if (!$doctor) {
            $doctor = new Doctor();
            $doctor->api_id = $api_id;
        }

        $response = $this->pinktreeApiService->getCDoctorInfo($api_id);
        $apiData = $response->successful() ? $response->json('data') : [];

        // Normalize and map fields
        $normalized = $this->normalizeDoctorApiData(is_array($apiData) ? $apiData : []);

        // Map normalized fields onto the $doctor object
        $fields = [
            'name', 'email', 'phone', 'gender', 'dob', 'age', 'degree', 'uniqueId', 'experience', 'placeName',
            'registrationNo', 'yearOfRegistration', 'recommendation', 'approvalStatus', 'createdAt', 'updatedAt',
        ];
        foreach ($fields as $field) {
            $doctor->{$field} = $normalized[$field] ?? null;
        }

        // Set service_ids for multi-select from normalized data
        $doctor->service_ids = isset($normalized['service_ids']) && is_array($normalized['service_ids']) ? $normalized['service_ids'] : [];

        // Fetch all services for the multi-select
        $servicesResponse = $this->pinktreeApiService->listServices();
        $services = $servicesResponse->successful() ? collect($servicesResponse->json('data'))->map(function($service) {
            return [
                'id' => $service['_id'] ?? null,
                'name' => $service['serviceName'] ?? '',
            ];
        })->all() : [];

        // Check Subscription Plan
        $planDetails = null;
        try {
            $planResponse = $this->pinktreeApiService->checkDoctorPlan($api_id);
            if ($planResponse->successful()) {
                $planDetails = $planResponse->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to check doctor plan: ' . $e->getMessage());
        }

        return view('doctors.edit', compact('doctor', 'services', 'planDetails'));
    }

    public function update(Request $request, $api_id)
    {
        // Try to find local doctor
        $doctor = Doctor::where('api_id', $api_id)->first();
        
        // If approvalStatus update is being requested (e.g., approve doctor), allow that
        if ($request->has('approvalStatus')) {
            $validated = $request->validate([
                'approvalStatus' => 'required|string',
            ]);

            $response = $this->pinktreeApiService->updateDoctor($api_id, ['approvalStatus' => $validated['approvalStatus']]);

            if ($response->failed()) {
                Log::error('Doctor ApprovalStatus Update - updateDoctor API Response (Failed):', ['body' => $response->body()]);
                // Try to extract friendly API message
                $msg = 'Failed to update Doctor approvalStatus via API.';
                try {
                    if (method_exists($response, 'json')) {
                        $rj = $response->json();
                        if (is_array($rj)) {
                            if (!empty($rj['message'])) $msg = $rj['message'];
                            elseif (!empty($rj['error'])) $msg = $rj['error'];
                            elseif (!empty($rj['errors'])) $msg = json_encode($rj['errors']);
                        }
                    }
                } catch (\Exception $e) {
                    // fall back to body
                    try { $msg = (string)$response->body(); } catch (\Exception $ex) {}
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 500);
                }
                return back()->withErrors(['error' => $msg])->withInput();
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Doctor approvalStatus updated successfully.']);
            }
            return back()->with('success', 'Doctor approvalStatus updated successfully.');
        }

        // Regular update logic
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'age' => 'nullable|integer|min:18|max:100',
            'degree' => 'nullable|string|max:255',
            'placeName' => 'nullable|string|max:255',
            'registrationNo' => 'nullable|string|max:255',
            'yearOfRegistration' => 'nullable|integer|min:1900|max:' . date('Y'),
            'email' => 'nullable|email|max:255',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'string',
            // Subscription fields
            'subscribe_plan' => 'nullable|boolean',
            'amount' => 'nullable|numeric',
            'years' => 'nullable|integer|min:1',
            'planId' => 'nullable|string',
        ]);

        $response = $this->pinktreeApiService->updateDoctor($api_id, $validatedData);

        if ($response->failed()) {
            Log::error('Doctor Update - updateDoctor API Response (Failed):', ['body' => $response->body()]);
            // Extract friendly message
            $msg = 'Failed to update Doctor via API.';
            try {
                if (method_exists($response, 'json')) {
                    $rj = $response->json();
                    if (is_array($rj)) {
                        if (!empty($rj['message'])) $msg = $rj['message'];
                        elseif (!empty($rj['error'])) $msg = $rj['error'];
                        elseif (!empty($rj['errors'])) $msg = json_encode($rj['errors']);
                    }
                }
            } catch (\Exception $e) {
                try { $msg = (string)$response->body(); } catch (\Exception $ex) {}
            }
            return back()->withErrors(['error' => $msg])->withInput();
        }

        // Subscription Plan
        if ($request->has('subscribe_plan') && $request->subscribe_plan && $doctor && $doctor->pharmaCompany) {
            $subData = [
                'pharmaId' => $doctor->pharmaCompany->api_id,
                'doctorId' => $api_id,
                'amount' => 1179, // Fixed: 999 + 18% GST
                'years' => 1,     // Fixed: 1 Year
                'planId' => $request->planId ?? null,
            ];
            // If planId is provided (from hidden input in edit form), it maps to _id in API for update
            if ($request->filled('planId')) {
                $subData['_id'] = $request->planId;
                unset($subData['planId']); // Ensure we send _id for updates if that's what API expects, or keep planId if API handles mapping. 
                // Based on previous context, update uses _id. subscribePlan method in service sends data as is.
                // Let's check subscribePlan in Service. It sends to /api/plan/subscribe.
                // If the API expects '_id' for update, we should set it.
                // The previous code in subscribe() method did: if ($request->filled('subscription_id')) { $subData['_id'] = ... }
            }
            
            $this->pinktreeApiService->subscribePlan($subData);
        }

        return redirect()->route('superadmin.doctors.index')->with('success', 'Doctor updated successfully.');
    }

    /**
     * Normalize API response keys into expected local keys.
     * Accepts the raw API data array and returns an array with keys we use in the views/controllers.
     */
    private function normalizeDoctorApiData(array $apiData): array
    {
        $aliases = [
            'name' => ['name', 'fullName', 'doctorName', 'doctor_name'],
            'email' => ['email', 'emailId', 'email_id'],
            'phone' => ['phone', 'mobileNo', 'mobile_no', 'phoneNumber', 'phone_no'],
            'dob' => ['dob', 'dateOfBirth', 'DOB', 'birthDate'],
            'age' => ['age'],
            'gender' => ['gender'],
            'degree' => ['degree', 'qualification'],
            'placeName' => ['placeName', 'place', 'place_name'],
            'registrationNo' => ['registrationNo', 'registration_number', 'regNo', 'registration_no'],
            'yearOfRegistration' => ['yearOfRegistration', 'year', 'year_of_registration'],
            'uniqueId' => ['uniqueId', 'unique_id'],
            'experience' => ['experience'],
            'recommendation' => ['recommendation'],
            'approvalStatus' => ['approvalStatus', 'approval_status'],
            'createdAt' => ['createdAt', 'created_at'],
            'updatedAt' => ['updatedAt', 'updated_at'],
            'service_ids' => ['service_ids', 'serviceIds', 'service_id', 'serviceId'],
        ];

        $result = [];
        foreach ($aliases as $key => $names) {
            $result[$key] = null;
            foreach ($names as $n) {
                if (array_key_exists($n, $apiData) && $apiData[$n] !== null) {
                    $result[$key] = $apiData[$n];
                    break;
                }
            }
        }

        // Ensure service_ids is an array
        if (!empty($result['service_ids']) && !is_array($result['service_ids'])) {
            // Try to decode JSON or split comma separated
            if (is_string($result['service_ids'])) {
                $maybe = json_decode($result['service_ids'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) {
                    $result['service_ids'] = $maybe;
                } else {
                    // comma separated
                    $result['service_ids'] = array_filter(array_map('trim', explode(',', $result['service_ids'])));
                }
            } else {
                $result['service_ids'] = [$result['service_ids']];
            }
        }

        return $result;
    }

    public function destroy($api_id)
    {
        $response = $this->pinktreeApiService->deleteDoctor($api_id);

        if ($response->failed()) {
            Log::error('Doctor Destroy - deleteDoctor API Response (Failed):', ['body' => $response->body()]);
            return back()->withErrors(['error' => 'Failed to delete Doctor via API.'])->withInput();
        }

        $doctor = Doctor::where('api_id', $api_id)->first();
        if ($doctor) {
            $doctor->delete();
        }

        return redirect()->route('superadmin.doctors.index')->with('success', 'Doctor deleted successfully.');
    }
}