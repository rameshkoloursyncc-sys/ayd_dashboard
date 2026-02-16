<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SponsorController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        try {
            $response = $this->pinktreeApiService->listAydSponsor();
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['sponsors']) && is_array($data['sponsors'])) {
                    $sponsors = $data['sponsors'];
                } elseif (isset($data['data']) && is_array($data['data'])) {
                    $sponsors = $data['data'];
                } elseif (is_array($data)) {
                    $sponsors = $data;
                } else {
                    $sponsors = [];
                }
            } else {
                Log::error('Sponsor Index - listAydSponsor API Response (Failed):', ['body' => $response->body()]);
                $sponsors = [];
                session()->flash('error', 'Could not retrieve sponsors from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch sponsors: ' . $e->getMessage());
            $sponsors = [];
            session()->flash('error', 'Could not connect to the API to fetch sponsors.');
        }
        
        return view('superadmin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        return view('superadmin.sponsors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sponsorText' => 'required',
        ]);

        try {
            $response = $this->pinktreeApiService->createSponsor([
                'sponsorText' => $request->sponsorText,
                'sponsorText2' => $request->sponsorText2,
                'sponsorLogo' => $request->sponsorLogo,
                'startSponsor' => $request->startSponsor ? $request->startSponsor . 'T00:00:00Z' : null,
                'endSponsor' => $request->endSponsor ? $request->endSponsor . 'T23:59:59Z' : null,
                'status' => $request->status ?? true,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.sponsors.index')->with('success', 'Sponsor created successfully.');
            } else {
                Log::error('Sponsor Store - createSponsor API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to create sponsor via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Sponsor creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during creation.'])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->pinktreeApiService->listAydSponsor();
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['sponsors']) && is_array($data['sponsors'])) {
                    $sponsors = $data['sponsors'];
                } elseif (isset($data['data']) && is_array($data['data'])) {
                    $sponsors = $data['data'];
                } elseif (is_array($data)) {
                    $sponsors = $data;
                } else {
                    $sponsors = [];
                }
                
                $sponsor = collect($sponsors)->firstWhere('_id', $id);
                
                if ($sponsor) {
                    return view('superadmin.sponsors.edit', compact('sponsor'));
                }
            }
            Log::error('Sponsor Edit - listAydSponsor API Response (Failed): Sponsor not found.', ['id' => $id, 'body' => $response->body()]);
            return redirect()->route('superadmin.sponsors.index')->withErrors(['error' => 'Sponsor not found.']);

        } catch (\Exception $e) {
            Log::error('Failed to fetch sponsor for edit: ' . $e->getMessage());
            return redirect()->route('superadmin.sponsors.index')->withErrors(['error' => 'Could not fetch sponsor details from the API.']);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sponsorText' => 'required',
        ]);

        try {
            $response = $this->pinktreeApiService->updateSponsor($id, [
                'sponsorText' => $request->sponsorText,
                'sponsorText2' => $request->sponsorText2,
                'sponsorLogo' => $request->sponsorLogo,
                'startSponsor' => $request->startSponsor ? $request->startSponsor . 'T00:00:00Z' : null,
                'endSponsor' => $request->endSponsor ? $request->endSponsor . 'T23:59:59Z' : null,
                'status' => $request->status ?? true,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.sponsors.index')->with('success', 'Sponsor updated successfully.');
            } else {
                Log::error('Sponsor Update - updateSponsor API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update sponsor via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Sponsor update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->pinktreeApiService->deleteSponsor($id);

            if ($response->successful()) {
                return redirect()->route('superadmin.sponsors.index')->with('success', 'Sponsor deleted successfully.');
            } else {
                Log::error('Sponsor Destroy - deleteSponsor API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete sponsor.']);
            }
        } catch (\Exception $e) {
            Log::error('Sponsor deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }
}
