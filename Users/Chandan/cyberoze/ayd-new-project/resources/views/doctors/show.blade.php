@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Doctor Details</h1>
    </div>

    <!-- Profile Card -->
    <div class="flex flex-col md:flex-row gap-6 mb-6">
        <div class="flex-shrink-0 flex flex-col items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-md p-6 w-full md:w-1/3">
            <div class="w-24 h-24 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-4xl font-bold text-primary-700 dark:text-primary-300 mb-4">
                {{ strtoupper(substr($doctor->name ?? 'D', 0, 1)) }}
            </div>
            <div class="text-xl font-semibold text-gray-900 dark:text-white mb-1">{{ $doctor->name ?? 'N/A' }}</div>
            <div class="text-gray-500 dark:text-gray-400 mb-2">{{ $doctor->degree ?? '' }}</div>
            <div class="flex flex-col gap-1 text-sm">
                <div><span class="font-medium">Email:</span> {{ $doctor->email ?? 'N/A' }}</div>
                <div><span class="font-medium">Phone:</span> {{ $doctor->phone ?? 'N/A' }}</div>
                <div><span class="font-medium">Gender:</span> {{ $doctor->gender ?? 'N/A' }}</div>
                <div><span class="font-medium">Age:</span> {{ $doctor->age ?? 'N/A' }}</div>
            </div>
            <div class="mt-3">
                @if(isset($doctor->approvalStatus))
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                        {{ $doctor->approvalStatus === 'approved' ? 'bg-green-100 text-green-800' : ($doctor->approvalStatus === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($doctor->approvalStatus) }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Details Table -->
        <div class="flex-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Personal & Professional Info</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <div><span class="font-medium">Unique ID:</span> {{ $doctor->uniqueId ?? 'N/A' }}</div>
                <div><span class="font-medium">DOB:</span> {{ $doctor->dob ? date('d M Y', strtotime($doctor->dob)) : 'N/A' }}</div>
                <div><span class="font-medium">Degree:</span> {{ $doctor->degree ?? 'N/A' }}</div>
                <div><span class="font-medium">Experience:</span> {{ $doctor->experience ?? 'N/A' }}</div>
                <div><span class="font-medium">Place Name:</span> {{ $doctor->placeName ?? 'N/A' }}</div>
                <div><span class="font-medium">Registration No:</span> {{ $doctor->registrationNo ?? 'N/A' }}</div>
                <div><span class="font-medium">Year of Registration:</span> {{ $doctor->yearOfRegistration ?? 'N/A' }}</div>
                <div><span class="font-medium">Recommendation:</span> {{ $doctor->recommendation ?? 'N/A' }}</div>
            </div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-6 mb-4">Affiliations</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <div><span class="font-medium">Pharma Company:</span> {{ $doctor->pharmaCompanyName ?? 'N/A' }}</div>
                <div><span class="font-medium">Medical Executive:</span> {{ $doctor->medicalExecutiveName ?? 'N/A' }}</div>
                <div><span class="font-medium">Services:</span>
                    @if(isset($doctor->service_names) && is_array($doctor->service_names) && count($doctor->service_names))
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1" style="white-space:normal;">
                            {{ implode(', ', $doctor->service_names) }}
                        </span>
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-6 mb-4">System Info</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <div><span class="font-medium">Created At:</span> {{ $doctor->createdAt ? date('d M Y, H:i', strtotime($doctor->createdAt)) : 'N/A' }}</div>
                <div><span class="font-medium">Updated At:</span> {{ $doctor->updatedAt ? date('d M Y, H:i', strtotime($doctor->updatedAt)) : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        @php
            $route = 'login'; // Default route
            if (Auth::user()->role === 'super_admin') {
                $route = 'superadmin.doctors.index';
            } elseif (Auth::user()->role === 'pharma_admin') {
                $route = 'pharma-admin.doctors.index';
            } elseif (Auth::user()->role === 'medical_executive') {
                $route = 'medical-executive.doctors.index';
            }
        @endphp
        <a href="{{ route($route) }}" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Back</a>
    </div>
</div>
@endsection