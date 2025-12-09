@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit My Company Details</h1>
        <a href="{{ route('pharma-admin.my-company.show') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to Company Details</a>
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

    @if (session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <form action="{{ route('pharma-admin.my-company.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="pharmaCoCode" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pharma Co Code</label>
                    <input type="text" name="pharmaCoCode" id="pharmaCoCode" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="PHARMA001" value="{{ old('pharmaCoCode', $pharmaCompany['pharmaCoCode'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" id="name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="ABC Pharma" value="{{ old('name', $pharmaCompany['name'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="speciality" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Specialities Signed Up For</label>
                    <select name="speciality[]" id="speciality" multiple class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                        @if(isset($services))
                            @foreach($services as $service)
                                <option value="{{ $service['_id'] }}" @if(collect(old('speciality', $pharmaCompany['specialitySignedUpFor'] ?? []))->contains($service['_id'])) selected @endif>
                                    {{ $service['serviceName'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <small class="text-gray-500">Hold Ctrl (Windows) or Command (Mac) to select multiple.</small>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="campaignTimeStartPeriod" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Campaign Start Date</label>
                    <input type="date" name="campaignTimeStartPeriod" id="campaignTimeStartPeriod" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('campaignTimeStartPeriod', $pharmaCompany['campaignTimeStartPeriod'] ?? '') }}" required="">
                    <div class="flex gap-2 mt-2">
                        <button type="button" class="set-campaign-days bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" data-days="30">30 days</button>
                        <button type="button" class="set-campaign-days bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" data-days="60">60 days</button>
                        <button type="button" class="set-campaign-days bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" data-days="90">90 days</button>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        function addDays(dateString, days) {
                            const date = new Date(dateString);
                            if (isNaN(date)) return '';
                            date.setDate(date.getDate() + days);
                            return date.toISOString().split('T')[0];
                        }
                        document.querySelectorAll('.set-campaign-days').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                const startInput = document.getElementById('campaignTimeStartPeriod');
                                const endInput = document.getElementById('campaignTimeEndPeriod');
                                if (startInput && endInput && startInput.value) {
                                    endInput.value = addDays(startInput.value, parseInt(btn.dataset.days));
                                } else {
                                    alert('Please select a campaign start date first.');
                                }
                            });
                        });
                    });
                    </script>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="campaignTimeEndPeriod" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Campaign End Date</label>
                    <input type="date" name="campaignTimeEndPeriod" id="campaignTimeEndPeriod" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('campaignTimeEndPeriod', $pharmaCompany['campaignTimeEndPeriod'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="digitalScratchCardConnectedTo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Digital Scratch Card Connected To</label>
                    <input type="text" name="digitalScratchCardConnectedTo" id="digitalScratchCardConnectedTo" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="ScratchCard001" value="{{ old('digitalScratchCardConnectedTo', $pharmaCompany['digitalScratchCardConnectedTo'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="advertisement" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Advertisement (Optional)</label>
                    <input type="text" name="advertisement" id="advertisement" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Ad001" value="{{ old('advertisement', $pharmaCompany['advertisement'] ?? '') }}">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="banner" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Banner (Optional)</label>
                    <select name="banner" id="banner" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="">Select a Banner</option>
                        @if(isset($banners))
                            @foreach($banners as $banner)
                                <option value="{{ $banner['_id'] }}" @if(old('banner', $pharmaCompany['banner'] ?? '') == $banner['_id']) selected @endif>
                                    {{ $banner['title'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="totalActivationQuota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Total Activation Quota</label>
                    <input type="number" name="totalActivationQuota" id="totalActivationQuota" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="5" value="{{ old('totalActivationQuota', $pharmaCompany['totalActivationQuota'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-full">
                    <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Update Company</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
