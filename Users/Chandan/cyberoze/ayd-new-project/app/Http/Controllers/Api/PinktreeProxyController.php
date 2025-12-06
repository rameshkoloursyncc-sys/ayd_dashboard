<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PinktreeApiService;

class PinktreeProxyController extends Controller
{
    protected $pinktreeApiService;

    public function __construct(PinktreeApiService $pinktreeApiService)
    {
        $this->pinktreeApiService = $pinktreeApiService;
    }

    // Proxy for fetching medical executives by pharma company API id
    public function getMedicalExecutivesByPharma($pharmaApiId)
    {
        $response = $this->pinktreeApiService->getMedicalPractitionerByPharma($pharmaApiId);
        return response()->json($response->json(), $response->status());
    }
}
