<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        try {
            $response = $this->pinktreeApiService->listServices();
            
            if ($response->successful()) {
                $data = $response->json();
                $services = $data['data'] ?? $data ?? [];
            } else {
                Log::error('Service Index - listServices API Response (Failed):', ['body' => $response->body()]);
                $services = [];
                session()->flash('error', 'Could not retrieve services from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch services: ' . $e->getMessage());
            $services = [];
            session()->flash('error', 'Could not connect to the API to fetch services.');
        }
        
        return view('superadmin.services.index', compact('services'));
    }

    public function create()
    {
        return view('superadmin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'serviceName' => 'required',
            'image' => 'required|url',
        ]);

        try {
            $response = $this->pinktreeApiService->createService([
                'serviceName' => $request->serviceName,
                'image' => $request->image,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.services.index')->with('success', 'Service created successfully.');
            } else {
                Log::error('Service Store - createService API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to create service via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Service creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during creation.'])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->pinktreeApiService->listServices();
            
            if ($response->successful()) {
                $data = $response->json();
                $services = $data['data'] ?? $data ?? [];
                $service = collect($services)->firstWhere('_id', $id);
                
                if ($service) {
                    return view('superadmin.services.edit', compact('service'));
                }
            }
            Log::error('Service Edit - listServices API Response (Failed): Service not found.', ['id' => $id, 'body' => $response->body()]);
            return redirect()->route('superadmin.services.index')->withErrors(['error' => 'Service not found.']);

        } catch (\Exception $e) {
            Log::error('Failed to fetch service for edit: ' . $e->getMessage());
            return redirect()->route('superadmin.services.index')->withErrors(['error' => 'Could not fetch service details from the API.']);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'serviceName' => 'required',
            'image' => 'required|url',
        ]);

        try {
            $response = $this->pinktreeApiService->updateService($id, [
                'serviceName' => $request->serviceName,
                'image' => $request->image,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.services.index')->with('success', 'Service updated successfully.');
            } else {
                Log::error('Service Update - updateService API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update service via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Service update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->pinktreeApiService->deleteService($id);

            if ($response->successful()) {
                return redirect()->route('superadmin.services.index')->with('success', 'Service deleted successfully.');
            } else {
                Log::error('Service Destroy - deleteService API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete service.']);
            }
        } catch (\Exception $e) {
            Log::error('Service deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }
}
