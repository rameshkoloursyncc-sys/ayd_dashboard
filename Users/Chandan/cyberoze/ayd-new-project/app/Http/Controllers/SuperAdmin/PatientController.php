<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        try {
            $response = $this->pinktreeApiService->getAYDPatientListFromFilters([]);
            
            if ($response->successful()) {
                $data = $response->json();
                // The API returns data nested in a 'data' key, and each item is wrapped in a stdClass object
                $rawPatients = $data['data'] ?? [];
                
                // Extract the actual patient data from the stdClass wrapper
                $patients = array_map(function($item) {
                    return $item['stdClass'] ?? $item;
                }, $rawPatients);
            } else {
                Log::error('Patient Index - getAYDPatientListFromFilters API Response (Failed):', ['body' => $response->body()]);
                $patients = [];
                session()->flash('error', 'Could not retrieve patients from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch patients: ' . $e->getMessage());
            $patients = [];
            session()->flash('error', 'Could not connect to the API to fetch patients.');
        }
        
        return view('superadmin.patients.index', compact('patients'));
    }

    public function create()
    {
        // Patient creation not supported by API - showing message
        return redirect()->route(auth()->user()->role == 'super_admin' ? 'superadmin.patients.index' : (auth()->user()->role == 'medical_executive' ? 'medical-executive.patients.index' : 'doctor.patients.index'))
            ->withErrors(['error' => 'Patient creation is not available in this system.']);
    }

    public function store(Request $request)
    {
        // Patient creation not supported by API
        return redirect()->back()->withErrors(['error' => 'Patient creation is not available in this system.']);
    }

    public function edit($id)
    {
        // Patient editing not supported by API - showing message
        $role = auth()->user()->role;
        $routeName = match($role) {
            'super_admin' => 'superadmin.patients.index',
            'medical_executive' => 'medical-executive.patients.index',
            'doctor' => 'doctor.patients.index',
            default => 'superadmin.patients.index'
        };
        return redirect()->route($routeName)->withErrors(['error' => 'Patient editing is not available in this system.']);
    }

    public function update(Request $request, $id)
    {
        // Patient updating not supported by API
        return redirect()->back()->withErrors(['error' => 'Patient updating is not available in this system.']);
    }

    public function destroy($id)
    {
        // Patient deletion not supported by API
        $role = auth()->user()->role;
        $routeName = match($role) {
            'super_admin' => 'superadmin.patients.index',
            'medical_executive' => 'medical-executive.patients.index',
            'doctor' => 'doctor.patients.index',
            default => 'superadmin.patients.index'
        };
        return redirect()->route($routeName)->withErrors(['error' => 'Patient deletion is not available in this system.']);
    }
}
