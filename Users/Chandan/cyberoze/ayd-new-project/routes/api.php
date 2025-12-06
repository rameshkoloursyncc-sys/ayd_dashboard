<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PinktreeProxyController;

Route::get('/pinktree/medical-executives/{pharmaApiId}', [PinktreeProxyController::class, 'getMedicalExecutivesByPharma']);
// (local medical-executives route removed — attach-to-pharma uses local DB via controller)
