<?php

namespace App\Http\Controllers\PharmaAdmin;

use App\Http\Controllers\Controller;
use App\Models\PharmaCompany;
use App\Models\MedicalExecutive;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $pharmaCompany = PharmaCompany::where('user_id', $user->id)->first();
        $medicalExecutivesCount = 0;
        $doctorsCount = 0;
        if ($pharmaCompany) {
            $medicalExecutivesCount = MedicalExecutive::where('pharma_company_id', $pharmaCompany->id)->count();
            $doctorsCount = Doctor::where('pharma_company_id', $pharmaCompany->id)->count();
        }
        return view('pharma-admin.dashboard', [
            'pharmaCompany' => $pharmaCompany,
            'medicalExecutivesCount' => $medicalExecutivesCount,
            'doctorsCount' => $doctorsCount,
        ]);
    }
}
