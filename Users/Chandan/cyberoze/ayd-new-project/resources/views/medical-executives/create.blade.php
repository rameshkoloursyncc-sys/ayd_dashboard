@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Medical Executive</h1>
        @if(Auth::user()->role === 'super_admin')
            <a href="{{ route('superadmin.medical-executives.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
        @elseif(Auth::user()->role === 'pharma_admin')
            <a href="{{ route('pharma-admin.medical-executives.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
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
        <form action="{{ Auth::user()->role === 'super_admin' ? route('superadmin.medical-executives.store') : route('pharma-admin.medical-executives.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" id="name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="John Doe" value="{{ old('name') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email" id="email" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="john.doe@example.com" value="{{ old('email') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="mobile_no" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mobile Number</label>
                    <input type="text" name="mobile_no" id="mobile_no" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="+1234567890" value="{{ old('mobile_no') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="whatsappNo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">WhatsApp Number</label>
                    <input type="text" name="whatsappNo" id="whatsappNo" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="+1234567890" value="{{ old('whatsappNo') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="age" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Age</label>
                    <input type="number" name="age" id="age" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="30" value="{{ old('age') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="gender" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gender</label>
                    <select name="gender" id="gender" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Select Gender</option>
                        <option value="Male" @if(old('gender') == 'Male') selected @endif>Male</option>
                        <option value="Female" @if(old('gender') == 'Female') selected @endif>Female</option>
                        <option value="Other" @if(old('gender') == 'Other') selected @endif>Other</option>
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="employeeNo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Employee Number</label>
                    <input type="text" name="employeeNo" id="employeeNo" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="EMP123" value="{{ old('employeeNo') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="city" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">City</label>
                    <input type="text" name="city" id="city" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Mumbai" value="{{ old('city') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="state" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">State</label>
                    <input type="text" name="state" id="state" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Maharashtra" value="{{ old('state') }}">
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
                <div class="col-span-6 sm:col-span-3">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="password" id="password" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="••••••••" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="••••••••" required="">
                </div>
                <div class="col-span-6 sm:col-full">
                    <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Add Medical Executive</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection