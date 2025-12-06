<?php

namespace App\Http\Controllers;

use App\Models\PharmaCompany;
use App\Models\User;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        $pharmaCompanies = [];
        $totalPharmaCompanies = 0;
        $totalMedicalExecutives = 0;
        $totalDoctors = 0;
        $totalPatients = 0;
        $totalServices = 0;
        $specialityLabels = [];
        $specialityData = [];
        $pharmaCompanyLabels = [];
        $doctorsByPharmaCompanyData = [];

        try {
            // Get pharma companies from API
            $pharmaResponse = $this->pinktreeApiService->getAllPharma();
            if ($pharmaResponse->successful()) {
                $pharmaCompanies = $pharmaResponse->json();
                $totalPharmaCompanies = count($pharmaCompanies);
            }

            // Get Patients from API
            $patientsResponse = $this->pinktreeApiService->getAYDPatientListFromFilters([]);
            if ($patientsResponse->successful()) {
                $patientData = $patientsResponse->json();
                $rawPatients = $patientData['data'] ?? [];
                $totalPatients = count($rawPatients);
            }

            // Get Services from API
            $servicesResponse = $this->pinktreeApiService->listServices();
            if ($servicesResponse->successful()) {
                $serviceData = $servicesResponse->json();
                $services = $serviceData['data'] ?? $serviceData ?? [];
                $totalServices = count($services);
            }

            // Get Medical Executives and Doctors from DB
            $totalMedicalExecutives = \App\Models\MedicalExecutive::count();
            $totalDoctors = \App\Models\Doctor::count();

            // Chart data processing
            $specialityCounts = [];
            $doctorsByPharmaCompany = [];
            foreach ($pharmaCompanies as $company) {
                $speciality = $company['specialitySignedUpFor'] ?? 'Unknown';
                if (!isset($specialityCounts[$speciality])) {
                    $specialityCounts[$speciality] = 0;
                }
                $specialityCounts[$speciality]++;

                // Doctors by pharma company
                $pharmaApiId = $company['_id'] ?? null;
                if ($pharmaApiId) {
                    $doctorsByPharmaCompany[$company['name'] ?? $pharmaApiId] = \App\Models\Doctor::where('pharma_company_id', function($q) use ($pharmaApiId) {
                        $q->select('id')->from('pharma_companies')->where('api_id', $pharmaApiId)->limit(1);
                    })->count();
                }
            }
            $specialityLabels = array_keys($specialityCounts);
            $specialityData = array_values($specialityCounts);
            $pharmaCompanyLabels = array_keys($doctorsByPharmaCompany);
            $doctorsByPharmaCompanyData = array_values($doctorsByPharmaCompany);

        } catch (\Exception $e) {
            Log::error('Could not connect to Pinktree API on dashboard: ' . $e->getMessage());
            session()->flash('error', 'Could not connect to the API. Some dashboard data may be missing.');
        }

        return view('superadmin.dashboard', compact(
            'totalPharmaCompanies', 'totalMedicalExecutives', 'totalDoctors', 'totalPatients', 'totalServices',
            'specialityLabels', 'specialityData', 'pharmaCompanyLabels', 'doctorsByPharmaCompanyData'
        ));
    }
}
