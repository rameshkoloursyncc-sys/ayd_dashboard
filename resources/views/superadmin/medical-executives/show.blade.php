@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Medical Executive Details</h1>
        <a href="{{ route('superadmin.medical-executives.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
    </div>

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                <p class="text-gray-900 dark:text-white">{{ $medicalExecutive->user->name }}</p>
            </div>
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <p class="text-gray-900 dark:text-white">{{ $medicalExecutive->user->email }}</p>
            </div>
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pharma Company</label>
                <p class="text-gray-900 dark:text-white">{{ $medicalExecutive->pharmaCompany->name }}</p>
            </div>
        </div>
    </div>
</div>
@endsection