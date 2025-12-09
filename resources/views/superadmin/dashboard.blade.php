@extends('layouts.app')

@section('content')
<div class="flex pt-16 overflow-hidden bg-gray-50 dark:bg-gray-900">

  @include('partials.sidebar')
  
  <div id="main-content" class="relative w-full h-full overflow-y-auto bg-gray-50 lg:ml-64 dark:bg-gray-900">
    <main>
      <div class="">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Super Admin Dashboard</h1>

        <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5 mb-4">
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">{{ $totalPharmaCompanies }}</span>
                        <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Pharma Companies</h3>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">{{ $totalMedicalExecutives }}</span>
                        <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Medical Executives</h3>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">{{ $totalDoctors }}</span>
                        <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Doctors</h3>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">{{ $totalPatients }}</span>
                        <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Patients</h3>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold leading-none text-gray-900 sm:text-3xl dark:text-white">{{ $totalServices }}</span>
                        <h3 class="text-base font-normal text-gray-500 dark:text-gray-400">Services</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Placeholder for charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Pharma Companies by Speciality</h2>
                <canvas id="pharmaCompanySpecialityChart"></canvas>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Doctors by Pharma Company</h2>
                <canvas id="doctorsByPharmaCompanyChart"></canvas>
            </div>
        </div>

      </div>
    </main>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const pharmaCompanySpecialityCtx = document.getElementById('pharmaCompanySpecialityChart').getContext('2d');
    new Chart(pharmaCompanySpecialityCtx, {
        type: 'bar',
        data: {
            labels: @json($specialityLabels),
            datasets: [{
                label: 'Number of Pharma Companies',
                data: @json($specialityData),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Doctors by Pharma Company
    const doctorsByPharmaCompanyCtx = document.getElementById('doctorsByPharmaCompanyChart').getContext('2d');
    new Chart(doctorsByPharmaCompanyCtx, {
        type: 'pie',
        data: {
            labels: @json($pharmaCompanyLabels),
            datasets: [{
                label: 'Number of Doctors',
                data: @json($doctorsByPharmaCompanyData),
                backgroundColor: [
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 99, 132, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 159, 64, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Distribution of Doctors by Pharma Company'
                }
            }
        },
    });
</script>
@endpush
