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

        } catch (\Exception $e) {
            Log::error('Could not connect to Pinktree API on dashboard: ' . $e->getMessage());
            session()->flash('error', 'Could not connect to the API. Some dashboard data may be missing.');
        }

        return view('superadmin.dashboard', compact(
            'totalPharmaCompanies', 'totalMedicalExecutives', 'totalDoctors', 'totalPatients', 'totalServices'
        ));
    }
}
