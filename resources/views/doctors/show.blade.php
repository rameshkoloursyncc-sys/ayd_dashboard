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
                @php $status = isset($doctor->approvalStatus) ? strtolower($doctor->approvalStatus) : null; @endphp
                @if(Auth::user()->role === 'super_admin' && $status !== 'approved')
                    <div class="mt-3">
                        <button id="approve-doctor-btn" data-api-id="{{ $doctor->api_id }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Approve Doctor</button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var btn = document.getElementById('approve-doctor-btn');
                            if (!btn) return;
                            btn.addEventListener('click', function() {
                                if (!confirm('Approve this doctor and set approvalStatus to Approved?')) return;
                                var apiId = btn.getAttribute('data-api-id');
                                var url = '/superadmin/doctors/' + apiId;
                                var body = new URLSearchParams();
                                body.append('approvalStatus', 'Approved');
                                fetch(url, {
                                    method: 'PUT',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: body.toString()
                                }).then(function(res){
                                    if (!res.ok) throw res;
                                    return res.json();
                                }).then(function(json){
                                    if (json.success) {
                                        btn.style.display = 'none';
                                        // update the status badge text
                                        var badge = btn.closest('.flex-shrink-0').querySelector('span');
                                        if (badge) badge.textContent = 'Approved';
                                        if (window.Swal) Swal.fire({icon:'success', title:'Approved', text: json.message || 'Doctor approved', timer:2000});
                                    } else {
                                        if (window.Swal) Swal.fire({icon:'error', title:'Error', text: json.message || 'Failed to approve'});
                                    }
                                }).catch(function(err){
                                    if (err.json) { err.json().then(function(j){ if (window.Swal) Swal.fire({icon:'error', title:'Error', text: j.message || 'Failed to approve'}); }).catch(function(){ if (window.Swal) Swal.fire({icon:'error', title:'Error', text: 'Failed to approve'}); }); }
                                    else { if (window.Swal) Swal.fire({icon:'error', title:'Error', text: 'Failed to approve'}); }
                                });
                            });
                        });
                    </script>
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

            <!-- Subscription Section -->
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-6 mb-4">Subscription Details</h2>
            @if(isset($planDetails) && !empty($planDetails) && isset($planDetails['status']) && $planDetails['status'] == true)
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-gray-700 dark:border-green-600">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-green-800 dark:text-green-300">Active Subscription</h3>
                    </div>
                    
                    @if(isset($planDetails['data']))
                        @php $plan = $planDetails['data']; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
                            @if(isset($plan['planName']))
                            <div><span class="font-semibold">Plan Name:</span> {{ $plan['planName'] }}</div>
                            @endif
                            @if(isset($plan['startDate']))
                            <div><span class="font-semibold">Start Date:</span> {{ \Carbon\Carbon::parse($plan['startDate'])->format('d M Y') }}</div>
                            @endif
                            @if(isset($plan['endDate']) || isset($plan['expireDate']))
                            <div><span class="font-semibold">End Date:</span> {{ \Carbon\Carbon::parse($plan['endDate'] ?? $plan['expireDate'])->format('d M Y') }}</div>
                            @endif
                            @if(isset($plan['amount']))
                            <div><span class="font-semibold">Amount:</span> ₹{{ $plan['amount'] }}</div>
                            @endif
                            @if(isset($plan['validity']))
                            <div><span class="font-semibold">Validity:</span> {{ $plan['validity'] }} Years</div>
                            @elseif(isset($plan['startDate']) && (isset($plan['endDate']) || isset($plan['expireDate'])))
                                @php
                                    $start = \Carbon\Carbon::parse($plan['startDate']);
                                    $end = \Carbon\Carbon::parse($plan['endDate'] ?? $plan['expireDate']);
                                    $diffInYears = $start->diffInYears($end);
                                @endphp
                                @if($diffInYears > 0)
                                <div><span class="font-semibold">Validity:</span> {{ $diffInYears }} Years</div>
                                @endif
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400">Plan is active.</p>
                    @endif
                </div>
                
                <!-- Price Breakdown (Blue Box) -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-gray-700 dark:border-blue-600">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-blue-800 dark:text-blue-300">Plan Details</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700 dark:text-gray-300">
                        <div><span class="font-semibold">Base Amount:</span> ₹999</div>
                        <div><span class="font-semibold">GST (18%):</span> ₹180</div>
                        <div><span class="font-semibold">Total Amount:</span> ₹1179</div>
                    </div>
                </div>
            @else
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg dark:bg-gray-700 dark:border-yellow-600">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-300">No Active Subscription</h3>
                    </div>
                    <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-400">This doctor does not have an active subscription plan.</p>
                </div>
            @endif

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