<?php

namespace App\Http\Controllers\MedicalExecutive;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $medicalExecutive = \App\Models\MedicalExecutive::where('user_id', $user->id)->first();
        $doctorsCount = 0;
        if ($medicalExecutive) {
            $doctorsCount = Doctor::where('medical_executive_id', $medicalExecutive->id)->count();
        }
        return view('medical-executive.dashboard', [
            'doctorsCount' => $doctorsCount,
        ]);
    }
}
