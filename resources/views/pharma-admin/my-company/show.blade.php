@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    @if(session('success'))
        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-primary-700 dark:text-primary-300 flex items-center gap-2">
            <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 4h6a2 2 0 002-2v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
            {{ $pharmaCompany['name'] ?? 'My Company' }}
        </h1>
        <a href="{{ route('pharma-admin.my-company.edit') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Edit Company</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex flex-col items-center border border-gray-200 dark:border-gray-700">
            @if(!empty($bannerUrl) && filter_var($bannerUrl, FILTER_VALIDATE_URL))
                <img src="{{ $bannerUrl }}" alt="Banner" class="w-full max-w-xs rounded shadow mb-4 border border-gray-200 dark:border-gray-700">
            @else
                <div class="w-full h-40 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded mb-4 text-gray-400">No Banner</div>
            @endif
            <div class="text-center">
                <span class="inline-block px-3 py-1 text-xs font-semibold bg-primary-100 text-primary-800 rounded-full dark:bg-primary-900 dark:text-primary-200 mb-2">Pharma Co Code: {{ $pharmaCompany['pharmaCoCode'] ?? 'N/A' }}</span>
                <div class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $pharmaCompany['name'] ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Speciality: <span class="font-semibold text-primary-700 dark:text-primary-300">{{ $specialityName ?? 'N/A' }}</span></div>
                <div class="flex flex-wrap gap-2 justify-center mt-2">
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded dark:bg-green-900 dark:text-green-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" /></svg>
                        Quota: {{ $pharmaCompany['totalActivationQuota'] ?? 'N/A' }}
                    </span>
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded dark:bg-blue-900 dark:text-blue-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4-4 4 4" /></svg>
                        Campaign: {{ $pharmaCompany['campaignTimeStartPeriod'] ?? 'N/A' }} - {{ $pharmaCompany['campaignTimeEndPeriod'] ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700 flex flex-col items-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                Advertisement
            </h2>
            <div class="text-lg text-gray-900 dark:text-white">
                {{ $advertisementValue ?? 'No Advertisement' }}
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" /></svg>
            Company Details
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Digital Scratch Card Connected To:</span>
                <span class="block text-base text-gray-900 dark:text-white">{{ $pharmaCompany['digitalScratchCardConnectedTo'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Campaign Start Date:</span>
                <span class="block text-base text-gray-900 dark:text-white">{{ $pharmaCompany['campaignTimeStartPeriod'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Campaign End Date:</span>
                <span class="block text-base text-gray-900 dark:text-white">{{ $pharmaCompany['campaignTimeEndPeriod'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Activation Quota:</span>
                <span class="block text-base text-gray-900 dark:text-white">{{ $pharmaCompany['totalActivationQuota'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
