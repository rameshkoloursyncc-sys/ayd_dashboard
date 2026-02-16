@extends('layouts.app')

@section('content')
@php
    $role = Auth::user()->role;
    $routePrefix = match($role) {
        'super_admin' => 'superadmin',
        'medical_executive' => 'medical-executive',
        'doctor' => 'doctor',
        default => 'superadmin'
    };
@endphp

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

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Manage Patients (Read-Only)</h1>
    </div>

    <div class="overflow-x-auto">
        <div class="inline-block min-w-full align-middle">
            <div class="overflow-hidden shadow sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                                Name
                            </th>
                            <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                                Email
                            </th>
                            <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                                Phone
                            </th>
                            <th scope="col" class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-white">
                                Services
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @forelse($patients as $patient)
                        <tr>
                            <td class="p-4 text-sm font-normal text-gray-900 whitespace-nowrap dark:text-white">
                                {{ ($patient['name'] ?? '') . ' ' . ($patient['lastName'] ?? '') }}
                            </td>
                            <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ $patient['email'] ?? 'N/A' }}
                            </td>
                            <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ $patient['phone'] ?? 'N/A' }}
                            </td>
                            <td class="p-4 text-sm font-normal text-gray-500 dark:text-gray-400">
                                @if(isset($patient['service_name']) && is_array($patient['service_name']))
                                    {{ implode(', ', $patient['service_name']) }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                No patients found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
