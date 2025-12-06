                <div class="col-span-6 sm:col-span-3">
                    <label for="city" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">City <span class="text-red-500">*</span></label>
                    <input type="text" name="city" id="city" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('city') }}" required>
                </div>
@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Doctor</h1>
        @if(Auth::user()->role === 'super_admin')
            <a href="{{ route('superadmin.doctors.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
        @elseif(Auth::user()->role === 'pharma_admin')
            <a href="{{ route('pharma-admin.doctors.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
        @elseif(Auth::user()->role === 'medical_executive')
            <a href="{{ route('medical-executive.doctors.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
        @endif
    </div>

    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        @php
            $storeRoute = '';
            if (Auth::user()->role === 'super_admin') {
                $storeRoute = route('superadmin.doctors.store');
            } elseif (Auth::user()->role === 'pharma_admin') {
                $storeRoute = route('pharma-admin.doctors.store');
            } elseif (Auth::user()->role === 'medical_executive') {
                $storeRoute = route('medical-executive.doctors.store');
            }
        @endphp
        <form action="{{ $storeRoute }}" method="POST">
            @csrf
            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Doctor Name</label>
                    <input type="text" name="name" id="name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Dr. Jane Doe" value="{{ old('name') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Doctor Email</label>
                    <input type="email" name="email" id="email" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="jane.doe@example.com" value="{{ old('email') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="mobile_no" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mobile Number</label>
                    <input type="text" name="mobile_no" id="mobile_no" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="+1234567890" value="{{ old('mobile_no') }}" required="">
                </div>
                
                @if(Auth::user()->role === 'super_admin')
                <div class="col-span-6 sm:col-span-3">
                    <label for="pharma_company_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pharma Company</label>
                    <select name="pharma_company_id" id="pharma_company_id" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                        <option value="">Select a Pharma Company</option>
                        @if(isset($pharmaCompanies))
                            @foreach($pharmaCompanies as $company)
                                <option value="{{ $company['id'] }}" @if(old('pharma_company_id') == $company['id']) selected @endif>
                                    {{ $company['name'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @endif

                @if(Auth::user()->role === 'super_admin')
                <div class="col-span-6 sm:col-span-3">
                    <label for="medical_executive_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign to Medical Executive (Optional)</label>
                    <select name="medical_executive_id" id="medical_executive_id" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Do not assign</option>
                    </select>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const pharmaSelect = document.getElementById('pharma_company_id');
                    const executiveSelect = document.getElementById('medical_executive_id');
                    pharmaSelect.addEventListener('change', function() {
                        const pharmaApiId = pharmaSelect.value;
                        executiveSelect.innerHTML = '<option value="">Loading...</option>';
                        if (!pharmaApiId) {
                            executiveSelect.innerHTML = '<option value="">Do not assign</option>';
                            return;
                        }
                        fetch(`/api/pinktree/medical-executives/${pharmaApiId}`)
                            .then(response => response.json())
                            .then(data => {
                                let options = '<option value="">Do not assign</option>';
                                if (Array.isArray(data.data)) {
                                    data.data.forEach(function(exec) {
                                        options += `<option value="${exec._id}">${exec.name}</option>`;
                                    });
                                }
                                executiveSelect.innerHTML = options;
                            })
                            .catch(() => {
                                executiveSelect.innerHTML = '<option value="">Do not assign</option>';
                            });
                    });
                });
                </script>
                @elseif(Auth::user()->role === 'pharma_admin')
                <div class="col-span-6 sm:col-span-3">
                    <label for="medical_executive_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign to Medical Executive (Optional)</label>
                    <select name="medical_executive_id" id="medical_executive_id" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Do not assign</option>
                        @if(isset($medicalExecutives))
                            @foreach($medicalExecutives as $executive)
                                <option value="{{ $executive->id }}" @if(old('medical_executive_id') == $executive->id) selected @endif>
                                    {{ $executive->user->name }} ({{ $executive->pharmaCompany->name }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @endif

                <input type="hidden" name="loginMode" value="OTP">
                <div class="col-span-6 sm:col-span-3">
                    <label for="dob" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('dob') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="age" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Age</label>
                    <input type="number" name="age" id="age" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="29" value="{{ old('age') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="gender" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gender</label>
                    <select name="gender" id="gender" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Select Gender</option>
                        <option value="Male" @if(old('gender')=='Male') selected @endif>Male</option>
                        <option value="Female" @if(old('gender')=='Female') selected @endif>Female</option>
                        <option value="Other" @if(old('gender')=='Other') selected @endif>Other</option>
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="degree" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Degree</label>
                    <input type="text" name="degree" id="degree" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="MBBS, MD, etc." value="{{ old('degree') }}">
                </div>
                <!-- ABHA removed per requirement -->
                <div class="col-span-6 sm:col-span-3">
                    <label for="placeName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Place Name <span class="text-red-500">*</span></label>
                    <input type="text" name="placeName" id="placeName" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Pune" value="{{ old('placeName') }}" required>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="registrationNo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Medical Registration Number <span class="text-red-500">*</span></label>
                    <input type="text" name="registrationNo" id="registrationNo" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="3424324242" value="{{ old('registrationNo') }}" required>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="yearOfRegistration" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Year of Registration</label>
                    <input type="text" name="yearOfRegistration" id="yearOfRegistration" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="2020" value="{{ old('yearOfRegistration') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="service_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Service</label>
                    <select name="service_id" id="service_id" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Select a Service</option>
                        @if(isset($services) && count($services) > 0)
                            @foreach($services as $service)
                                <option value="{{ $service['id'] }}" @if(old('service_id') == $service['id']) selected @endif>{{ $service['name'] }}</option>
                            @endforeach
                        @else
                            <option value="">No services available</option>
                        @endif
                    </select>
                </div>
                <div class="col-span-6 sm:col-full">
                    <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Add Doctor</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
