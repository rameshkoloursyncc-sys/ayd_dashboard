<?php

namespace App\Http\Controllers\PharmaAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalExecutiveRequest;
use App\Services\MedicalExecutiveCreationService;
use App\Services\PinktreeApiService;
use App\Models\MedicalExecutive;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        $pharma_company_id = Auth::user()->pharma_company_id;
        $medicalExecutives = MedicalExecutive::where('pharma_company_id', $pharma_company_id)->with('user')->get();
        return view('pharma-admin.medical-executives.index', compact('medicalExecutives'));
    }

    public function create()
    {
        return view('medical-executives.create');
    }

    public function store(StoreMedicalExecutiveRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['pharma_company_id'] = Auth::user()->pharma_company_id;

        try {
            $this->creationService->create($validatedData);
            return redirect()->route('pharma-admin.medical-executives.index')->with('success', 'Medical Executive created successfully.');
        } catch (\Exception $e) {
            Log::error('Medical Executive creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create Medical Executive: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(MedicalExecutive $medicalExecutive)
    {
        $this->authorize('view', $medicalExecutive);
        $medicalExecutive->load('user');
        return view('pharma-admin.medical-executives.show', compact('medicalExecutive'));
    }

    public function edit(MedicalExecutive $medicalExecutive)
    {
        $this->authorize('update', $medicalExecutive);
        return view('pharma-admin.medical-executives.edit', compact('medicalExecutive'));
    }

    public function update(Request $request, MedicalExecutive $medicalExecutive)
    {
        $this->authorize('update', $medicalExecutive);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($medicalExecutive->user_id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            // Update external API
            $response = $this->pinktreeApiService->updateMedicalExecutive($medicalExecutive->api_id, [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => $validatedData['password'] ?? null,
            ]);

            if ($response->failed()) {
                Log::error('Medical Executive Update - updateMedicalExecutive API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update Medical Executive via API.'])->withInput();
            }

            // Update local user
            $user = $medicalExecutive->user;
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            if (!empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }
            $user->save();
    
            return redirect()->route('pharma-admin.medical-executives.index')->with('success', 'Medical Executive updated successfully.');

        } catch (\Exception $e) {
            Log::error('Medical Executive update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy(MedicalExecutive $medicalExecutive)
    {
        $this->authorize('delete', $medicalExecutive);

        try {
            $response = $this->pinktreeApiService->deleteMedicalPractitioner($medicalExecutive->api_id);
            if ($response->failed()) {
                Log::error('Medical Executive Destroy - deleteMedicalPractitioner API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete Medical Executive via API.'])->withInput();
            }

                        $medicalExecutive->delete();
            
                        return redirect()->route('pharma-admin.medical-executives.index')->with('success', 'Medical Executive deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Medical Executive deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }
}
