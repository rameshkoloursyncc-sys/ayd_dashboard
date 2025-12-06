<?php

namespace App\Http\Controllers\PharmaAdmin;

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
        $pharmaCompanyId = Auth::user()->pharma_company_id;
        $localDoctors = Doctor::where('pharma_company_id', $pharmaCompanyId)
            ->with(['pharmaCompany', 'medicalExecutive.user'])
            ->get();

        $doctors = $localDoctors->map(function ($localDoctor) {
            $apiData = (object)['name' => 'N/A', 'email' => 'N/A'];
            $response = $this->pinktreeApiService->getCDoctorInfo($localDoctor->api_id);
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

            // Get medical executive name from local relationship
            $medicalExecutiveName = 'N/A';
            if ($localDoctor->medicalExecutive && $localDoctor->medicalExecutive->user) {
                $medicalExecutiveName = $localDoctor->medicalExecutive->user->name;
            }

            return (object)[
                'name' => $apiData->name,
                'email' => $apiData->email,
                'pharmaCompanyName' => $pharmaCompanyName,
                'medicalExecutiveName' => $medicalExecutiveName,
                'api_id' => $localDoctor->api_id,
            ];
        });

        return view('pharma-admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $viewData = [];
        try {
            $pharmaCompanyId = Auth::user()->pharma_company_id;
            $viewData['pharmaCompanies'] = \App\Models\PharmaCompany::all();
            $viewData['medicalExecutives'] = \App\Models\MedicalExecutive::where('pharma_company_id', $pharmaCompanyId)->with('user')->get();
            $servicesResponse = app(\App\Services\PinktreeApiService::class)->listServices();
            $viewData['services'] = $servicesResponse->successful() ? collect($servicesResponse->json('data'))->map(function($service) {
                return [
                    'id' => $service['_id'] ?? null,
                    'name' => $service['serviceName'] ?? '',
                ];
            })->all() : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch data for Pharma Admin Doctor creation: ' . $e->getMessage());
            $viewData['pharmaCompanies'] = [];
            $viewData['medicalExecutives'] = [];
            $viewData['services'] = [];
            session()->flash('error', 'Could not fetch necessary data for doctor creation.');
        }
        return view('doctors.create', $viewData);
    }

    public function store(StoreDoctorRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['pharma_company_id'] = Auth::user()->pharma_company_id;

        try {
            $this->creationService->create($validatedData);

            return redirect()->route('pharma-admin.doctors.index')->with('success', 'Doctor created successfully.');

        } catch (\Exception $e) {
            Log::error('Doctor creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create Doctor: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Doctor $doctor)
    {
        if (Auth::user()->pharma_company_id !== $doctor->pharma_company_id) {
            abort(403);
        }

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
        if (Auth::user()->pharma_company_id !== $doctor->pharma_company_id) {
            abort(403);
        }

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

        // Optionally fetch and set medicalExecutiveName if not present
        $doctor->medicalExecutiveName = 'N/A';
        if ($doctor->medical_executive_id) {
            $localMedicalExecutive = $doctor->medicalExecutive;
            if ($localMedicalExecutive && $localMedicalExecutive->user) {
                $doctor->medicalExecutiveName = $localMedicalExecutive->user->name;
            }
        }

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
        if (Auth::user()->pharma_company_id !== $doctor->pharma_company_id) {
            abort(403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);

        $response = $this->pinktreeApiService->updateDoctor($doctor->api_id, $validatedData);

        if ($response->failed()) {
            Log::error('Doctor Update - updateDoctor API Response (Failed):', ['body' => $response->body()]);
            return back()->withErrors(['error' => 'Failed to update Doctor via API.'])->withInput();
        }

        return redirect()->route('pharma-admin.doctors.index')->with('success', 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor)
    {
        if (Auth::user()->pharma_company_id !== $doctor->pharma_company_id) {
            abort(403);
        }

        $response = $this->pinktreeApiService->deleteDoctor($doctor->api_id);

        if ($response->failed()) {
            Log::error('Doctor Destroy - deleteDoctor API Response (Failed):', ['body' => $response->body()]);
            return back()->withErrors(['error' => 'Failed to delete Doctor via API.'])->withInput();
        }

        $doctor->delete();

        return redirect()->route('pharma-admin.doctors.index')->with('success', 'Doctor deleted successfully.');
    }
}
