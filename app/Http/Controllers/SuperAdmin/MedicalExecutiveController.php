<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalExecutiveRequest;
use App\Services\MedicalExecutiveCreationService;
use App\Services\PinktreeApiService;
use App\Models\MedicalExecutive;
use App\Models\User;
use App\Models\PharmaCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class MedicalExecutiveController extends Controller
{
    protected $creationService;
    protected $pinktreeApiService;

    public function __construct(MedicalExecutiveCreationService $creationService, PinktreeApiService $pinktreeApiService)
    {
        $this->creationService = $creationService;
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        $medicalExecutives = MedicalExecutive::with('user', 'pharmaCompany')->get();
        $executivesWithPharmaName = $medicalExecutives->map(function ($executive) {
            $pharmaName = 'N/A';
            if ($executive->pharmaCompany && $executive->pharmaCompany->api_id) {
                $apiService = app(\App\Services\PinktreeApiService::class);
                $response = $apiService->getByPharmaId($executive->pharmaCompany->api_id);
                if ($response->successful() && isset($response->json('data')['name'])) {
                    $pharmaName = $response->json('data')['name'];
                }
            }
            $executive->pharma_company_name = $pharmaName;
            return $executive;
        });

        $pharmaCompanies = PharmaCompany::with('user')->get();

        return view('superadmin.medical-executives.index', [
            'medicalExecutives' => $executivesWithPharmaName,
            'pharmaCompanies' => $pharmaCompanies
        ]);
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
        } catch (\Exception $e) {
            Log::error('Failed to fetch pharma companies for Super Admin ME creation: ' . $e->getMessage());
            $viewData['pharmaCompanies'] = [];
            session()->flash('error', 'Could not fetch pharma companies.');
        }
        return view('medical-executives.create', $viewData);
    }

    public function store(StoreMedicalExecutiveRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $this->creationService->create($validatedData);

            return redirect()->route('superadmin.medical-executives.index')->with('success', 'Medical Executive created successfully.');

        } catch (\Exception $e) {
            Log::error('Medical Executive creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create Medical Executive: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(MedicalExecutive $medicalExecutive)
    {
        $medicalExecutive->load('user', 'pharmaCompany');
        return view('superadmin.medical-executives.show', compact('medicalExecutive'));
    }

    public function edit(MedicalExecutive $medicalExecutive)
    {
        $pharmaCompanies = PharmaCompany::with('user')->get();
        return view('superadmin.medical-executives.edit', compact('medicalExecutive', 'pharmaCompanies'));
    }

    public function update(Request $request, MedicalExecutive $medicalExecutive)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $medicalExecutive->user_id,
            'pharma_company_id' => 'required|exists:pharma_companies,id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            // Update external API
            $apiData = [
                'name' => $validatedData['name'],
                'emailId' => $validatedData['email'],
            ];

            if (!empty($validatedData['password'])) {
                $apiData['password'] = $validatedData['password'];
            }

            $response = $this->pinktreeApiService->updateMedicalExecutive($medicalExecutive->api_id, $apiData);

            if ($response->failed()) {
                Log::error('Medical Executive Update - updateMedicalExecutive API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update Medical Executive via API.'])->withInput();
            }

            $user = $medicalExecutive->user;
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            if (!empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }
            $user->save();

            $medicalExecutive->pharma_company_id = $validatedData['pharma_company_id'];
            $medicalExecutive->save();

            return redirect()->route('superadmin.medical-executives.index')->with('success', 'Medical Executive updated successfully.');
        } catch (\Exception $e) {
            Log::error('Medical Executive update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update Medical Executive: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(MedicalExecutive $medicalExecutive)
    {
        try {
            $response = $this->pinktreeApiService->deleteMedicalPractitioner($medicalExecutive->api_id);
            if ($response->failed()) {
                Log::error('Medical Executive Destroy - deleteMedicalPractitioner API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete Medical Executive via API.'])->withInput();
            }

            $medicalExecutive->delete();

            return redirect()->route('superadmin.medical-executives.index')->with('success', 'Medical Executive deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Medical Executive deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }

    public function getByPharma($pharmaApiId)
    {
        $pharma = PharmaCompany::where('api_id', $pharmaApiId)->first();
        if (!$pharma) {
            return response()->json(['data' => []]);
        }
        
        $executives = MedicalExecutive::where('pharma_company_id', $pharma->id)
            ->with('user')
            ->get()
            ->map(function($exec) {
                return [
                    '_id' => $exec->id, // Use local ID for assignment
                    'name' => $exec->user->name ?? 'Unknown',
                ];
            });

        return response()->json(['data' => $executives]);
    }
}