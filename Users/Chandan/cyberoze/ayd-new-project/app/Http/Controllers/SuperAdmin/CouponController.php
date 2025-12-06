<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PinktreeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    public function index()
    {
        try {
            $response = $this->pinktreeApiService->listAllChatCoupon();
            
            if ($response->successful()) {
                $data = $response->json();
                $coupons = $data['data'] ?? $data ?? [];
            } else {
                Log::error('Coupon Index - listAllChatCoupon API Response (Failed):', ['body' => $response->body()]);
                $coupons = [];
                session()->flash('error', 'Could not retrieve coupons from the API.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch coupons: ' . $e->getMessage());
            $coupons = [];
            session()->flash('error', 'Could not connect to the API to fetch coupons.');
        }
        
        return view('superadmin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('superadmin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'code' => 'required',
            'threshold' => 'required|numeric',
            'discount' => 'required|numeric',
            'limitAccess' => 'required|integer',
            'pendingLimit' => 'required|integer',
            'couponType' => 'required|in:flat,percentage',
            'accessType' => 'required|in:public,private',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'status' => 'required|boolean',
        ]);

        try {
            $response = $this->pinktreeApiService->createChatCoupon([
                'title' => $request->title,
                'description' => $request->description,
                'code' => $request->code,
                'threshold' => $request->threshold,
                'discount' => $request->discount,
                'limitAccess' => $request->limitAccess,
                'pendingLimit' => $request->pendingLimit,
                'couponType' => $request->couponType,
                'accessType' => $request->accessType,
                'startDate' => $request->startDate . 'T00:00:00Z',
                'endDate' => $request->endDate . 'T23:59:59Z',
                'status' => $request->status,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.coupons.index')->with('success', 'Coupon created successfully.');
            } else {
                Log::error('Coupon Store - createChatCoupon API Response (Failed):', ['body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to create coupon via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Coupon creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during creation.'])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->pinktreeApiService->listAllChatCoupon();
            
            if ($response->successful()) {
                $data = $response->json();
                $coupons = $data['data'] ?? $data ?? [];
                $coupon = collect($coupons)->firstWhere('_id', $id);
                
                if ($coupon) {
                    return view('superadmin.coupons.edit', compact('coupon'));
                }
            }
            Log::error('Coupon Edit - listAllChatCoupon API Response (Failed): Coupon not found.', ['id' => $id, 'body' => $response->body()]);
            return redirect()->route('superadmin.coupons.index')->withErrors(['error' => 'Coupon not found.']);

        } catch (\Exception $e) {
            Log::error('Failed to fetch coupon for edit: ' . $e->getMessage());
            return redirect()->route('superadmin.coupons.index')->withErrors(['error' => 'Could not fetch coupon details from the API.']);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'code' => 'required',
            'threshold' => 'required|numeric',
            'discount' => 'required|numeric',
            'limitAccess' => 'required|integer',
            'pendingLimit' => 'required|integer',
            'couponType' => 'required|in:flat,percentage',
            'accessType' => 'required|in:public,private',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'status' => 'required|boolean',
        ]);

        try {
            $response = $this->pinktreeApiService->updateChatCoupon($id, [
                'title' => $request->title,
                'description' => $request->description,
                'code' => $request->code,
                'threshold' => $request->threshold,
                'discount' => $request->discount,
                'limitAccess' => $request->limitAccess,
                'pendingLimit' => $request->pendingLimit,
                'couponType' => $request->couponType,
                'accessType' => $request->accessType,
                'startDate' => $request->startDate . 'T00:00:00Z',
                'endDate' => $request->endDate . 'T23:59:59Z',
                'status' => $request->status,
            ]);

            if ($response->successful()) {
                return redirect()->route('superadmin.coupons.index')->with('success', 'Coupon updated successfully.');
            } else {
                Log::error('Coupon Update - updateChatCoupon API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to update coupon via API.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Coupon update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during update.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->pinktreeApiService->deleteChatCoupon($id);

            if ($response->successful()) {
                return redirect()->route('superadmin.coupons.index')->with('success', 'Coupon deleted successfully.');
            } else {
                Log::error('Coupon Destroy - deleteChatCoupon API Response (Failed):', ['id' => $id, 'body' => $response->body()]);
                return back()->withErrors(['error' => 'Failed to delete coupon.']);
            }
        } catch (\Exception $e) {
            Log::error('Coupon deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred during deletion.'])->withInput();
        }
    }
}
