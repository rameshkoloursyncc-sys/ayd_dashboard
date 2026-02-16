<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdmin\PharmaCompanyController;
use App\Http\Controllers\SuperAdmin\ServiceController;
use App\Http\Controllers\SuperAdmin\BannerController;
use App\Http\Controllers\SuperAdmin\CouponController;
use App\Http\Controllers\SuperAdmin\FaqController;
use App\Http\Controllers\SuperAdmin\SponsorController;
use App\Http\Controllers\SuperAdmin\PatientController;
use App\Http\Controllers\SuperAdmin\ProfileController;
use App\Http\Controllers\SuperAdmin\MedicalExecutiveController as SuperAdminMedicalExecutiveController;
use App\Http\Controllers\PharmaAdmin\MedicalExecutiveController as PharmaAdminMedicalExecutiveController;
use App\Http\Controllers\SuperAdmin\DoctorController as SuperAdminDoctorController;
use App\Http\Controllers\PharmaAdmin\DoctorController as PharmaAdminDoctorController;
use App\Http\Controllers\MedicalExecutive\DoctorController as MedicalExecutiveDoctorController;
use App\Http\Controllers\PharmaAdmin\MyCompanyController;


Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        switch ($user->role) {
            case 'super_admin':
                return redirect()->intended(route('superadmin.dashboard'));
            case 'pharma_admin':
                // Redirect Pharma Admin to their company page
                $pharmaCompany = \App\Models\PharmaCompany::where('user_id', $user->id)->first();
                if ($pharmaCompany) {
                    return redirect()->intended(route('pharma-admin.my-company.show'));
                }
                return redirect()->intended(route('pharma-admin.dashboard'));
            case 'medical_executive':
                return redirect()->intended(route('medical-executive.dashboard'));
            case 'doctor':
                return redirect()->intended('/pharma-dashboard');
            default:
                return redirect()->route('login');
        }
    } else {
        return redirect()->route('login');
    }
});

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'is_super_admin'])->name('superadmin.')->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');

    // Pharma Companies Routes
    Route::get('pharma-companies', [PharmaCompanyController::class, 'index'])->name('pharma-companies.index');
    Route::get('pharma-companies/create', [PharmaCompanyController::class, 'create'])->name('pharma-companies.create');
    Route::post('pharma-companies', [PharmaCompanyController::class, 'store'])->name('pharma-companies.store');
    Route::get('pharma-companies/{pharma_company_api_id}', [PharmaCompanyController::class, 'show'])->name('pharma-companies.show');
    Route::get('pharma-companies/{pharma_company_api_id}/edit', [PharmaCompanyController::class, 'edit'])->name('pharma-companies.edit');
    Route::put('pharma-companies/{pharma_company_api_id}', [PharmaCompanyController::class, 'update'])->name('pharma-companies.update');
    Route::delete('pharma-companies/{pharma_company_api_id}', [PharmaCompanyController::class, 'destroy'])->name('pharma-companies.destroy');
    Route::post('pharma-companies/{pharma_company_api_id}/link-local', [PharmaCompanyController::class, 'linkLocal'])->name('pharma-companies.linkLocal');


    Route::resource('services', ServiceController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('faq', FaqController::class);
    Route::resource('sponsors', SponsorController::class);
    Route::resource('patients', PatientController::class);
    Route::resource('medical-executives', SuperAdminMedicalExecutiveController::class);
    Route::get('medical-executives/by-pharma/{pharmaApiId}', [SuperAdminMedicalExecutiveController::class, 'getByPharma'])->name('medical-executives.byPharma');
    Route::resource('doctors', SuperAdminDoctorController::class);

    // Attach doctor to pharma company
    Route::post('doctors/{api_id}/attach-pharma', [SuperAdminDoctorController::class, 'attachPharma'])->name('doctors.attachPharma');
    // Assign subscription to doctor
    Route::post('doctors/{api_id}/subscribe', [SuperAdminDoctorController::class, 'subscribe'])->name('doctors.subscribe');
    // Mark payout as paid
    Route::post('doctors/{api_id}/payout', [SuperAdminDoctorController::class, 'storePayout'])->name('doctors.storePayout');

    // Bank Account Routes
    Route::post('doctors/bank-account', [SuperAdminDoctorController::class, 'storeBankAccount'])->name('doctors.storeBankAccount');
    Route::put('doctors/bank-account/{accountId}', [SuperAdminDoctorController::class, 'updateBankAccount'])->name('doctors.updateBankAccount');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::put('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

Route::middleware(['auth', 'role:pharma_admin'])->name('pharma-admin.')->prefix('pharma-admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\PharmaAdmin\DashboardController::class, 'index'])->name('dashboard');
    // My Company Routes
    Route::get('/my-company', [MyCompanyController::class, 'show'])->name('my-company.show');
    Route::get('/my-company/edit', [MyCompanyController::class, 'edit'])->name('my-company.edit');
    Route::put('/my-company', [MyCompanyController::class, 'update'])->name('my-company.update');
    Route::resource('medical-executives', PharmaAdminMedicalExecutiveController::class);
    Route::resource('doctors', PharmaAdminDoctorController::class);
    // Profile/Password routes for Pharma Admin
    Route::get('/profile/password', [\App\Http\Controllers\SuperAdmin\ProfileController::class, 'changePassword'])->name('profile.password');
    Route::put('/profile/password/update', [\App\Http\Controllers\SuperAdmin\ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

Route::middleware(['auth', 'role:medical_executive'])->name('medical-executive.')->prefix('medical-executive')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\MedicalExecutive\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('doctors', MedicalExecutiveDoctorController::class);
    // Profile/Password routes for Medical Executive
    Route::get('/profile/password', [\App\Http\Controllers\SuperAdmin\ProfileController::class, 'changePassword'])->name('profile.password');
    Route::put('/profile/password/update', [\App\Http\Controllers\SuperAdmin\ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// QR Code AJAX Route
Route::middleware(['auth'])->get('/doctor/{api_id}/qr', [\App\Http\Controllers\SuperAdmin\DoctorController::class, 'getDoctorQRView'])->name('doctor.qr.ajax');
