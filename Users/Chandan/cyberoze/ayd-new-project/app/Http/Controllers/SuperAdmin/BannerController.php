<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        try {
            $response = $this->pinktreeApiService->listAydBanner();
            
            if ($response->successful()) {
                $data = $response->json();
                $banners = $data['data'] ?? $data ?? [];
            } else {
                Log::error('Banner Index - listAydBanner API Response (Failed):', ['body' => $response->body()]);
                $banners = [];
                session()->flash('error', 'Could not retrieve banners from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch banners: ' . $e->getMessage());
            $banners = [];
            session()->flash('error', 'Could not connect to the API to fetch banners.');
        }
        
        return view('superadmin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('superadmin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'required|url',
            'link' => 'nullable|url',
            'status' => 'required|boolean',
            'priority' => 'required|integer',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        try {
            $response = $this->pinktreeApiService->createAydBanner([
                'title' => $request->title,
                'image' => $request->image,
                'link' => $request->link,
                'status' => $request->status,
                'priority' => $request->priority,
                'startDate' => $request->startDate . 'T00:00:00Z',
                'endDate' => $request->endDate . 'T23:59:59Z',
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.banners.index')->with('success', 'Banner created successfully.');
            } else {
                Log::error('Banner Store - createAydBanner API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to create banner via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Banner creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during creation.'])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->pinktreeApiService->listAydBanner();
            
            if ($response->successful()) {
                $data = $response->json();
                $banners = $data['data'] ?? $data ?? [];
                $banner = collect($banners)->firstWhere('_id', $id);
                
                if ($banner) {
                    return view('superadmin.banners.edit', compact('banner'));
                }
            }
            Log::error('Banner Edit - listAydBanner API Response (Failed): Banner not found.', ['id' => $id, 'body' => $response->body()]);
            return redirect()->route('superadmin.banners.index')->withErrors(['error' => 'Banner not found.']);

        } catch (\Exception $e) {
            Log::error('Failed to fetch banner for edit: ' . $e->getMessage());
            return redirect()->route('superadmin.banners.index')->withErrors(['error' => 'Could not fetch banner details from the API.']);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'required|url',
            'link' => 'nullable|url',
            'status' => 'required|boolean',
            'priority' => 'required|integer',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        try {
            $response = $this->pinktreeApiService->updateAydBanner($id, [
                'title' => $request->title,
                'image' => $request->image,
                'link' => $request->link,
                'status' => $request->status,
                'priority' => $request->priority,
                'startDate' => $request->startDate . 'T00:00:00Z',
                'endDate' => $request->endDate . 'T23:59:59Z',
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.banners.index')->with('success', 'Banner updated successfully.');
            } else {
                Log::error('Banner Update - updateAydBanner API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update banner via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Banner update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->pinktreeApiService->deleteAydBanner($id);

            if ($response->successful()) {
                return redirect()->route('superadmin.banners.index')->with('success', 'Banner deleted successfully.');
            } else {
                Log::error('Banner Destroy - deleteAydBanner API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete banner.']);
            }
        } catch (\Exception $e) {
            Log::error('Banner deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }
}
