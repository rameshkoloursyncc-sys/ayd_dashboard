@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Doctor</h1>
        <a href="{{ route('superadmin.doctors.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
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
        <form action="{{ route('superadmin.doctors.update', $doctor->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" id="name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('name', $doctor->user->name) }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email" id="email" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('email', $doctor->user->email) }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="pharma_company_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pharma Company</label>
                    <select name="pharma_company_id" id="pharma_company_id" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                        @foreach($pharmaCompanies as $company)
                            <option value="{{ $company->id }}" @if(old('pharma_company_id', $doctor->pharma_company_id) == $company->id) selected @endif>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="medical_executive_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Medical Executive</label>
                    <select name="medical_executive_id" id="medical_executive_id" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">None</option>
                        @foreach($medicalExecutives as $executive)
                            <option value="{{ $executive->id }}" @if(old('medical_executive_id', $doctor->medical_executive_id) == $executive->id) selected @endif>
                                {{ $executive->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(isset($doctor) && $doctor->pharmaCompany)
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
                    (function(){
                        var cb = document.getElementById('subscribe_plan');
                        if (!cb) return;
                        cb.addEventListener('change', function() {
                            var fields = document.getElementById('subscription_fields');
                            if (this.checked) fields.classList.remove('hidden'); else fields.classList.add('hidden');
                        });
                    })();
                </script>
                @endif
                <div class="col-span-6 sm:col-span-3">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">New Password (optional)</label>
                    <input type="password" name="password" id="password" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                </div>
                <div class="col-span-6 sm:col-full">
                    <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Update Doctor</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
