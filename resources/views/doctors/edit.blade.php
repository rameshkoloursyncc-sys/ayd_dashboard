                <div class="col-span-6 sm:col-span-3">
                    <label for="city" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">City <span class="text-red-500">*</span></label>
                    <input type="text" name="city" id="city" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('city', $doctor->city ?? '') }}" required>
                </div>
@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Doctor</h1>
    </div>

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
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
                    <label for="service_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Service</label>
                    <select name="service_id" id="service_id" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Select a Service</option>
                        @if(isset($services) && count($services) > 0)
                            @foreach($services as $service)
                                <option value="{{ $service['id'] }}" @if(isset($doctor->service_id) && $doctor->service_id == $service['id']) selected @endif>{{ $service['name'] }}</option>
                            @endforeach
                        @else
                            <option value="">No services available</option>
                        @endif
                    </select>
                </div>
                <div class="col-span-6 sm:col-full">
                    <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Update Doctor</button>
                    <a href="{{ route($indexRoute) }}" class="ml-4 text-gray-700 hover:underline dark:text-gray-300">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection