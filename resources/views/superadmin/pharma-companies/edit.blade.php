@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Pharma Company</h1>
        <a href="{{ route('superadmin.pharma-companies.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
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
        <form action="{{ route('superadmin.pharma-companies.update', $pharmaCompany['_id']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="pharma_co_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pharma Co Code</label>
                    <input type="text" name="pharma_co_code" id="pharma_co_code" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="PHARMA001" value="{{ old('pharma_co_code', $pharmaCompany['pharmaCoCode'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" id="name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="ABC Pharma" value="{{ old('name', $pharmaCompany['name'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="speciality" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Specialities Signed Up For</label>
                    @php
                        $currentSpecialities = old('speciality', $pharmaCompany['specialitySignedUpFor'] ?? []);
                        if (!is_array($currentSpecialities)) {
                            $currentSpecialities = $currentSpecialities ? [$currentSpecialities] : [];
                        }
                    @endphp
                    <select name="speciality[]" id="speciality" multiple class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 h-40 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                        @if(isset($services) && count($services) > 0)
                            @foreach($services as $service)
                                <option value="{{ $service['_id'] }}" @if(in_array($service['_id'], $currentSpecialities)) selected @endif>
                                    {{ $service['serviceName'] }}
                                </option>
                            @endforeach
                        @else
                            <option disabled>No specialities available</option>
                        @endif
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</p>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="campaign_start_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Campaign Start Date</label>
                    <input type="date" name="campaign_start_date" id="campaign_start_date" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('campaign_start_date', $pharmaCompany['campaignTimeStartPeriod'] ?? '') }}" required="">
                    <div class="flex gap-2 mt-2" id="campaign-duration-buttons" style="display:none;">
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

                        const startInput = document.getElementById('campaign_start_date');
                        const endInput = document.getElementById('campaign_end_date');
                        const buttonsContainer = document.getElementById('campaign-duration-buttons');

                        function updateButtonsVisibility() {
                            if (startInput && startInput.value) {
                                buttonsContainer.style.display = 'flex';
                            } else {
                                buttonsContainer.style.display = 'none';
                            }
                        }

                        if (startInput) {
                            updateButtonsVisibility();
                            startInput.addEventListener('change', updateButtonsVisibility);
                            startInput.addEventListener('input', updateButtonsVisibility);
                        }

                        document.querySelectorAll('.set-campaign-days').forEach(function(btn) {
                            btn.addEventListener('click', function() {
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
                    <label for="campaign_end_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Campaign End Date</label>
                    <input type="date" name="campaign_end_date" id="campaign_end_date" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('campaign_end_date', $pharmaCompany['campaignTimeEndPeriod'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="unique_code_pool" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Digital Scratch Card Connected To</label>
                    <input type="text" name="unique_code_pool" id="unique_code_pool" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="ScratchCard001" value="{{ old('unique_code_pool', $pharmaCompany['digitalScratchCardConnectedTo'] ?? '') }}" required="">
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
                    <label for="total_activation_quota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Total Activation Quota</label>
                    <input type="number" name="total_activation_quota" id="total_activation_quota" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="5" value="{{ old('total_activation_quota', $pharmaCompany['totalActivationQuota'] ?? '') }}" required="">
                </div>
                <div class="col-span-6 sm:col-full">
                    <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Update Company</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection