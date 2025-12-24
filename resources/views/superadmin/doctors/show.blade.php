@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Doctor Details</h1>
        <a href="{{ route('superadmin.doctors.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
    </div>

    @php $status = isset($doctor->approvalStatus) ? strtolower($doctor->approvalStatus) : null; @endphp
    @if(Auth::user()->role === 'super_admin' && $status !== 'approved')
        <div class="mb-4">
            <button id="approve-doctor-btn" data-api-id="{{ $doctor->api_id }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">Approve Doctor</button>
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
                            if (window.Swal) Swal.fire({icon:'success', title:'Approved', text: json.message || 'Doctor approved', timer:2000});
                            // update any status badge on page
                            var badge = document.querySelector('.text-gray-900');
                            if (badge) badge.textContent = 'Approved';
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

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->name ?? 'N/A' }}</p>
            </div>
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->email ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mobile Number</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->phone ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date of Birth</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->dob ? \Carbon\Carbon::parse($doctor->dob)->format('d M Y') : 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Age</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->age ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gender</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->gender ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Degree</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->degree ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Place Name</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->placeName ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Registration No.</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->registrationNo ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Year of Registration</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->yearOfRegistration ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Service</label>
                <p class="text-gray-900 dark:text-white">{{ (isset($doctor->service_names) && count($doctor->service_names) > 0) ? implode(', ', $doctor->service_names) : ($doctor->service_id ?? 'N/A') }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pharma Company</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->pharmaCompanyName ?? 'N/A' }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Medical Executive</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->medicalExecutiveName ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Subscription Section -->
    <div class="mt-6 p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h2>
        
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

        @php
            $hasActivePlan = isset($planDetails) && !empty($planDetails) && isset($planDetails['status']) && $planDetails['status'] == true;
            $activePlan = $hasActivePlan && isset($planDetails['data']) ? $planDetails['data'] : null;
            
            $defaultYears = '1';
            if ($hasActivePlan) {
                if (isset($activePlan['validity'])) {
                    $defaultYears = $activePlan['validity'];
                } elseif (isset($activePlan['startDate']) && (isset($activePlan['endDate']) || isset($activePlan['expireDate']))) {
                    $start = \Carbon\Carbon::parse($activePlan['startDate']);
                    $end = \Carbon\Carbon::parse($activePlan['endDate'] ?? $activePlan['expireDate']);
                    $diff = $start->diffInYears($end);
                    if ($diff > 0) $defaultYears = $diff;
                }
            }
        @endphp

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $hasActivePlan ? 'Update Subscription' : 'Assign Subscription' }}</h2>
        
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-gray-700 dark:border-blue-600">
            <h3 class="font-medium text-blue-800 dark:text-blue-300 mb-2">Plan Details: Standard Annual Plan</h3>
            <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                <li>Base Price: ₹999</li>
                <li>GST (18%): ₹180</li>
                <li class="font-bold mt-1">Total Payable: ₹1179</li>
            </ul>
        </div>

        <form action="{{ route('superadmin.doctors.subscribe', $doctor->api_id) }}" method="POST">
            @csrf
            @if($hasActivePlan && isset($activePlan['_id']))
                <input type="hidden" name="subscription_id" value="{{ $activePlan['_id'] }}">
            @endif
            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Amount (999 + 18% GST)</label>
                    <input type="number" name="amount" id="amount" value="1179" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed" readonly>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="years" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration (Years)</label>
                    <input type="number" name="years" id="years" value="1" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed" readonly>
                </div>
                <div class="col-span-6">
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">{{ $hasActivePlan ? 'Update Subscription' : 'Assign Subscription' }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection