<?php

namespace App\Http\Controllers\MedicalExecutive;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Services\DoctorCreationService;
use App\Services\PinktreeApiService;
use App\Models\MedicalExecutive;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    protected $creationService;
    protected $pinktreeApiService;

    public function __construct(DoctorCreationService $creationService, PinktreeApiService $pinktreeApiService)
    {
        $this->creationService = $creationService;
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        $user = Auth::user();
        $medicalExecutive = MedicalExecutive::where('user_id', $user->id)->first();

        if (!$medicalExecutive) {
            return back()->withErrors(['error' => 'Medical Executive record not found for the current user.'])->withInput();
        }

        $localDoctors = Doctor::where('medical_executive_id', $medicalExecutive->id)
                                ->with('pharmaCompany')
                                ->get();

        Log::info('Pharma Company for first doctor:', ['pharma_company' => $localDoctors->first() ? $localDoctors->first()->pharmaCompany : 'No local doctors found']);

        $doctors = $localDoctors->map(function ($localDoctor) {
            $apiData = (object)['name' => 'N/A', 'email' => 'N/A'];
            $response = $this->pinktreeApiService->getCDoctorInfo($localDoctor->api_id);
            Log::info('Get Doctor Info API Response:', ['body' => $response->body()]);
            if ($response->successful() && isset($response->json('data')['name'])) {
                $apiData->name = $response->json('data')['name'];
                $apiData->email = $response->json('data')['email'] ?? 'N/A';
            }

            $pharmaCompanyName = 'N/A';
            if ($localDoctor->pharmaCompany) {
                $pharmaResponse = $this->pinktreeApiService->getByPharmaId($localDoctor->pharmaCompany->api_id);
                if ($pharmaResponse->successful() && isset($pharmaResponse->json('data')['name'])) {
                    $pharmaCompanyName = $pharmaResponse->json('data')['name'];
                }
            }

            return (object)[
                'name' => $apiData->name,
                'email' => $apiData->email,
                'pharmaCompanyName' => $pharmaCompanyName,
                'api_id' => $localDoctor->api_id,
            ];
        });

        return view('medical-executive.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $viewData = [];
        try {
            $viewData['pharmaCompanies'] = \App\Models\PharmaCompany::all();
            $viewData['medicalExecutives'] = \App\Models\MedicalExecutive::with('user')->get();
            $servicesResponse = app(\App\Services\PinktreeApiService::class)->listServices();
            $viewData['services'] = $servicesResponse->successful() ? collect($servicesResponse->json('data'))->map(function($service) {
                return [
                    'id' => $service['_id'] ?? null,
                    'name' => $service['serviceName'] ?? '',
                ];
            })->all() : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch services for Medical Executive Doctor creation: ' . $e->getMessage());
            $viewData['pharmaCompanies'] = [];
            $viewData['medicalExecutives'] = [];
            $viewData['services'] = [];
            session()->flash('error', 'Could not fetch services for doctor creation.');
        }
        return view('doctors.create', $viewData);
    }

    public function store(StoreDoctorRequest $request)
    {
        $validatedData = $request->validated();
        $user = Auth::user();

        $medicalExecutive = MedicalExecutive::where('user_id', $user->id)->first();
        if ($medicalExecutive) {
            $validatedData['medical_executive_id'] = $medicalExecutive->id;
            $validatedData['pharma_company_id'] = $medicalExecutive->pharma_company_id;
        } else {
            return back()->withErrors(['error' => 'Medical Executive record not found for the current user.'])->withInput();
        }

        try {
            $this->creationService->create($validatedData);

            return redirect()->route('medical-executive.doctors.index')->with('success', 'Doctor created successfully.');

        } catch (\Exception $e) {
            Log::error('Doctor creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create Doctor: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Doctor $doctor)
    {
        Log::info('Showing doctor with api_id:', ['api_id' => $doctor->api_id]);
        $foundDoctor = Doctor::where('api_id', $doctor->api_id)->first();
        Log::info('Doctor found in DB:', ['doctor' => $foundDoctor]);

        if (Auth::id() !== $doctor->medicalExecutive->user_id) {
            abort(403);
        }

        // Debug log for API data and what is sent to the view
        Log::info('DoctorController@show: API doctor data', ['api_id' => $doctor->api_id]);

        // Fetch all doctor data from API
        $apiData = [];
        $response = $this->pinktreeApiService->getCDoctorInfo($doctor->api_id);
        if ($response->successful() && is_array($response->json('data'))) {
            $apiData = $response->json('data');
        } elseif ($response->successful() && is_object($response->json('data'))) {
            $apiData = (array) $response->json('data');
        }

        Log::info('DoctorController@show: API response data', ['apiData' => $apiData]);

        // Get pharma company name
        $pharmaCompanyName = 'N/A';
        if ($doctor->pharmaCompany) {
            $pharmaResponse = $this->pinktreeApiService->getByPharmaId($doctor->pharmaCompany->api_id);
            if ($pharmaResponse->successful() && isset($pharmaResponse->json('data')['name'])) {
                $pharmaCompanyName = $pharmaResponse->json('data')['name'];
            }
        }

        Log::info('DoctorController@show: Pharma company name', ['pharmaCompanyName' => $pharmaCompanyName]);

        // Get medical executive name
        $medicalExecutiveName = null;
        if ($doctor->medicalExecutive && $doctor->medicalExecutive->user) {
            $medicalExecutiveName = $doctor->medicalExecutive->user->name;
        }

        Log::info('DoctorController@show: Medical executive name', ['medicalExecutiveName' => $medicalExecutiveName]);

        // Replace service_ids with service names
        $serviceNames = [];
        if (!empty($apiData['service_ids']) && is_array($apiData['service_ids'])) {
            $servicesResponse = $this->pinktreeApiService->listServices();
            $servicesData = $servicesResponse->json('data');
            if ($servicesResponse->successful() && null !== $servicesData) {
                $allServices = $servicesData;
                $serviceMap = collect($allServices)->pluck('serviceName', '_id');
                foreach ($apiData['service_ids'] as $sid) {
                    if ($serviceMap->has($sid)) {
                        $serviceNames[] = $serviceMap[$sid];
                    }
                }
            }
        }
        $apiData['service_names'] = $serviceNames;
        unset($apiData['service_ids']);

        Log::info('DoctorController@show: Service names', ['service_names' => $serviceNames]);

        // Add pharma company and medical executive name
        $apiData['pharmaCompanyName'] = $pharmaCompanyName;
        $apiData['medicalExecutiveName'] = $medicalExecutiveName;

        Log::info('DoctorController@show: Final data sent to view', ['doctor' => $apiData]);

        return view('doctors.show', ['doctor' => (object)$apiData]);
    }

    public function edit(Doctor $doctor)
    {
        if (Auth::id() !== $doctor->medicalExecutive->user_id) {
            abort(403);
        }

        // Fetch all doctor data from API for editing
        $apiData = [];
        $response = $this->pinktreeApiService->getCDoctorInfo($doctor->api_id);
        if ($response->successful() && is_array($response->json('data'))) {
            $apiData = $response->json('data');
        } elseif ($response->successful() && is_object($response->json('data'))) {
            $apiData = (array) $response->json('data');
        }

        // Add local doctor id for route
        $apiData['api_id'] = $doctor->api_id;

        // Fetch services for the multi-select
        $services = [];
        try {
            $servicesResponse = $this->pinktreeApiService->listServices();
            $services = $servicesResponse->successful() ? collect($servicesResponse->json('data'))->map(function($service) {
                return [
                    'id' => $service['_id'] ?? null,
                    'name' => $service['serviceName'] ?? '',
                ];
            })->all() : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch services for Doctor edit: ' . $e->getMessage());
            $services = [];
        }

        return view('doctors.edit', ['doctor' => (object)$apiData, 'services' => $services]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        if (Auth::id() !== $doctor->medicalExecutive->user_id) {
            abort(403);
        }

        // Accept all editable fields from the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
            'age' => 'nullable|integer',
            'degree' => 'nullable|string|max:100',
            'uniqueId' => 'nullable|string',
            // Add more fields as needed
        ]);

        try {
            // Update external API with all editable fields
            $response = $this->pinktreeApiService->updateDoctor($doctor->api_id, $validatedData);

            if ($response->failed()) {
                Log::error('Doctor Update - updateDoctor API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update Doctor via API.'])->withInput();
            }

            return redirect()->route('medical-executive.doctors.index')->with('success', 'Doctor updated successfully.');

        } catch (\Exception $e) {
            Log::error('Doctor update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy(Doctor $doctor)
    {
        if (Auth::id() !== $doctor->medicalExecutive->user_id) {
            abort(403);
        }

        try {
            $response = $this->pinktreeApiService->deleteDoctor($doctor->api_id);
            if ($response->failed()) {
                Log::error('Doctor Destroy - deleteDoctor API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete Doctor via API.'])->withInput();
            }

            $doctor->delete();

            return redirect()->route('medical-executive.doctors.index')->with('success', 'Doctor deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Doctor deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }
}
