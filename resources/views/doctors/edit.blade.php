@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Doctor</h1>
    </div>

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        
        <!-- Subscription Status Display -->
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
            $updateRoute = 'login'; // Default route
            $indexRoute = 'login'; // Default route
            if (Auth::user()->role === 'super_admin') {
                $updateRoute = 'superadmin.doctors.update';
                $indexRoute = 'superadmin.doctors.index';
            } elseif (Auth::user()->role === 'pharma_admin') {
                $updateRoute = 'pharma-admin.doctors.update';
                $indexRoute = 'pharma-admin.doctors.index';
            } elseif (Auth::user()->role === 'medical_executive') {
                $updateRoute = 'medical-executive.doctors.update';
                $indexRoute = 'medical-executive.doctors.index';
            }
        @endphp
        <form action="{{ route($updateRoute, $doctor->api_id) }}" method="POST">
            @csrf
            @method('PUT')


            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Doctor Name</label>
                    <input type="text" name="name" id="name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->name }}" required>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Doctor Email</label>
                    <input type="email" name="email" id="email" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->email }}" required>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mobile Number</label>
                    <input type="text" name="phone" id="phone" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->phone }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="dob" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->dob ? date('Y-m-d', strtotime($doctor->dob)) : '' }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="age" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Age</label>
                    <input type="number" name="age" id="age" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->age }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="gender" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gender</label>
                    <select name="gender" id="gender" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Select Gender</option>
                        <option value="Male" @if($doctor->gender=='Male') selected @endif>Male</option>
                        <option value="Female" @if($doctor->gender=='Female') selected @endif>Female</option>
                        <option value="Other" @if($doctor->gender=='Other') selected @endif>Other</option>
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="degree" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Degree</label>
                    <input type="text" name="degree" id="degree" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->degree }}">
                </div>
                <!-- ABHA removed per requirement -->
                <div class="col-span-6 sm:col-span-3">
                    <label for="placeName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Place Name <span class="text-red-500">*</span></label>
                    <input type="text" name="placeName" id="placeName" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->placeName }}" required>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="registrationNo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Medical Registration Number <span class="text-red-500">*</span></label>
                    <input type="text" name="registrationNo" id="registrationNo" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->registrationNo }}" required>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="yearOfRegistration" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Year of Registration</label>
                    <input type="text" name="yearOfRegistration" id="yearOfRegistration" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $doctor->yearOfRegistration }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="service_ids" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Service</label>
                    @php
                        $selectedServices = old('service_ids', $doctor->service_ids ?? []);
                        if (!is_array($selectedServices)) $selectedServices = [$selectedServices];
                    @endphp
                    <select name="service_ids[]" id="service_ids" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Select a Service</option>
                        @if(isset($services) && count($services) > 0)
                            @foreach($services as $service)
                                <option value="{{ $service['id'] }}" @if(in_array($service['id'], $selectedServices)) selected @endif>{{ $service['name'] }}</option>
                            @endforeach
                        @else
                            <option value="">No services available</option>
                        @endif
                    </select>
                </div>

                @if($doctor->pharmaCompany)
                <!-- Subscription Plan Fields -->
                <div class="col-span-6 sm:col-span-3 flex items-center mt-6">
                    <input id="subscribe_plan" name="subscribe_plan" type="checkbox" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" @if(isset($planDetails) && !empty($planDetails) && isset($planDetails['status']) && $planDetails['status'] == true) checked @endif>
                    <label for="subscribe_plan" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Assign/Update Subscription</label>
                </div>
                
                <div id="subscription_fields" class="col-span-6 grid grid-cols-6 gap-6 @if(!(isset($planDetails) && !empty($planDetails) && isset($planDetails['status']) && $planDetails['status'] == true)) hidden @endif">
                    @php
                        $hasActivePlan = isset($planDetails) && !empty($planDetails) && isset($planDetails['status']) && $planDetails['status'] == true;
                        $activePlan = $hasActivePlan && isset($planDetails['data']) ? $planDetails['data'] : null;
                    @endphp

                    <div class="col-span-6 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-gray-700 dark:border-blue-600">
                        <h3 class="font-medium text-blue-800 dark:text-blue-300 mb-2">Plan Details: Standard Annual Plan</h3>
                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                            <li>Base Price: ₹999</li>
                            <li>GST (18%): ₹180</li>
                            <li class="font-bold mt-1">Total Payable: ₹1179</li>
                        </ul>
                    </div>
                    
                    @if($hasActivePlan && isset($activePlan['_id']))
                        <input type="hidden" name="planId" value="{{ $activePlan['_id'] }}">
                    @endif

                    <div class="col-span-6 sm:col-span-3">
                        <label for="amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Amount (999 + 18% GST)</label>
                        <input type="number" name="amount" id="amount" class="shadow-sm bg-gray-100 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed" value="1179" readonly>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="years" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration (Years)</label>
                        <input type="number" name="years" id="years" class="shadow-sm bg-gray-100 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed" value="1" readonly>
                    </div>
                </div>

                <script>
                    document.getElementById('subscribe_plan').addEventListener('change', function() {
                        const fields = document.getElementById('subscription_fields');
                        if (this.checked) {
                            fields.classList.remove('hidden');
                        } else {
                            fields.classList.add('hidden');
                        }
                    });
                </script>
                @endif

                <div class="col-span-6 sm:col-full">
                    <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Update Doctor</button>
                    <a href="{{ route($indexRoute) }}" class="ml-4 text-gray-700 hover:underline dark:text-gray-300">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection