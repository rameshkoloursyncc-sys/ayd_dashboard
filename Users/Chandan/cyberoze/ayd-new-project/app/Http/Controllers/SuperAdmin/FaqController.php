<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaqController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        try {
            $response = $this->pinktreeApiService->listAYDFaq();
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['faqs']) && is_array($data['faqs'])) {
                    $faqs = $data['faqs'];
                } elseif (isset($data['data']) && is_array($data['data'])) {
                    $faqs = $data['data'];
                } elseif (is_array($data)) {
                    $faqs = $data;
                } else {
                    $faqs = [];
                }
            } else {
                Log::error('FAQ Index - listAYDFaq API Response (Failed):', ['body' => $response->body()]);
                $faqs = [];
                session()->flash('error', 'Could not retrieve FAQs from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch FAQs: ' . $e->getMessage());
            $faqs = [];
            session()->flash('error', 'Could not connect to the API to fetch FAQs.');
        }
        
        return view('superadmin.faq.index', compact('faqs'));
    }

    public function create()
    {
        return view('superadmin.faq.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'answer' => 'required',
        ]);

        try {
            $response = $this->pinktreeApiService->createAYDFaq([
                'question' => $request->question,
                'answer' => $request->answer,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.faq.index')->with('success', 'FAQ created successfully.');
            } else {
                Log::error('FAQ Store - createAYDFaq API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to create FAQ via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('FAQ creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during creation.'])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->pinktreeApiService->listAYDFaq();
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['faqs']) && is_array($data['faqs'])) {
                    $faqs = $data['faqs'];
                } elseif (isset($data['data']) && is_array($data['data'])) {
                    $faqs = $data['data'];
                } elseif (is_array($data)) {
                    $faqs = $data;
                } else {
                    $faqs = [];
                }
                
                $faq = collect($faqs)->firstWhere('_id', $id);
                
                if ($faq) {
                    return view('superadmin.faq.edit', compact('faq'));
                }
            }
            Log::error('FAQ Edit - listAYDFaq API Response (Failed): FAQ not found.', ['id' => $id, 'body' => $response->body()]);
            return redirect()->route('superadmin.faq.index')->withErrors(['error' => 'FAQ not found.']);

        } catch (\Exception $e) {
            Log::error('Failed to fetch FAQ for edit: ' . $e->getMessage());
            return redirect()->route('superadmin.faq.index')->withErrors(['error' => 'Could not fetch FAQ details from the API.']);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required',
            'answer' => 'required',
        ]);

        try {
            $response = $this->pinktreeApiService->updateAYDFaq($id, [
                'question' => $request->question,
                'answer' => $request->answer,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.faq.index')->with('success', 'FAQ updated successfully.');
            } else {
                Log::error('FAQ Update - updateAYDFaq API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update FAQ via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('FAQ update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->pinktreeApiService->deleteAYDFaq($id);

            if ($response->successful()) {
                return redirect()->route('superadmin.faq.index')->with('success', 'FAQ deleted successfully.');
            } else {
                Log::error('FAQ Destroy - deleteAYDFaq API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete FAQ.']);
            }
        } catch (\Exception $e) {
            Log::error('FAQ deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }
}
