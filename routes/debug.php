<?php

use Illuminate\Support\Facades\Route;
use App\Models\Doctor;

Route::get('/debug-doctors', function () {
    $doctors = Doctor::all();
    return response()->json($doctors);
});
