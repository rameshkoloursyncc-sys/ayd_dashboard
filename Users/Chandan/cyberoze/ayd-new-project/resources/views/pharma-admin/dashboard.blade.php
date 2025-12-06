@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Pharma Admin Dashboard</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-400 to-purple-400 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-lg font-semibold mb-2">My Company</h2>
            <p class="text-base text-gray-900 dark:text-white mb-2">{{ $pharmaCompany->id ? $pharmaCompany->id : 'N/A' }} - {{ $pharmaCompany->user_id ? $pharmaCompany->user_id : 'N/A' }}</p>
            <a href="{{ route('pharma-admin.my-company.show') }}" class="text-blue-600 hover:underline">View Company Details</a>
        </div>
        <div class="bg-gradient-to-r from-green-400 to-teal-400 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-lg font-semibold mb-2">Medical Executives</h2>
            <p class="text-2xl font-bold">{{ $medicalExecutivesCount }}</p>
            <a href="{{ route('pharma-admin.medical-executives.index') }}" class="text-blue-600 hover:underline">View All</a>
        </div>
        <div class="bg-gradient-to-r from-pink-400 to-red-400 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-lg font-semibold mb-2">Doctors</h2>
            <p class="text-2xl font-bold">{{ $doctorsCount }}</p>
            <a href="{{ route('pharma-admin.doctors.index') }}" class="text-blue-600 hover:underline">View All</a>
        </div>
    </div>
</div>
@endsection
