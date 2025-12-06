@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Medical Executive Dashboard</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Doctors</h2>
            <p class="text-2xl font-bold">{{ $doctorsCount }}</p>
            <a href="{{ route('medical-executive.doctors.index') }}" class="text-blue-600 hover:underline">View All</a>
        </div>
    </div>
</div>
@endsection
