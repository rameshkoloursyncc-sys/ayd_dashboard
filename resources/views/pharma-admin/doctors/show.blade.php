@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Doctor Details</h1>
        <a href="{{ route('pharma-admin.doctors.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
    </div>

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800 mb-4">
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->user->name }}</p>
            </div>
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->user->email }}</p>
            </div>
            <div class="col-span-6 sm:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Medical Executive</label>
                <p class="text-gray-900 dark:text-white">{{ $doctor->medicalExecutive->user->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- QR Code Section -->
    <div class="p-4 bg-white border border-dashed border-gray-300 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Doctor QR Code</h3>
            <button id="load-qr-btn" type="button" data-doctor-id="{{ $doctor->api_id }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                Show QR
            </button>
        </div>
        <div id="qr-code-container" class="flex items-center justify-center min-h-[80px]">
            <p class="text-xs text-gray-500 dark:text-gray-400">Click "Show QR" to load the code.</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('load-qr-btn');
        var qrContainer = document.getElementById('qr-code-container');

        if (!btn || !qrContainer) return;

        btn.addEventListener('click', function() {
            var doctorId = btn.getAttribute('data-doctor-id');
            if (!doctorId) {
                console.error('No doctorId found on load-qr-btn (pharma-admin)');
                return;
            }

            console.log('Fetching QR for doctorId (pharma-admin):', doctorId);
            qrContainer.innerHTML = '<svg class="animate-spin h-6 w-6 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            fetch('https://ayd.pinktreehealth.com/api/qr/' + doctorId)
                .then(function(res) {
                    console.log('QR API raw response status (pharma-admin):', res.status);
                    if (!res.ok) throw new Error('API request failed');
                    return res.json();
                })
                .then(function(json) {
                    console.log('QR API parsed JSON (pharma-admin):', json);
                    if (json.status && json.data && json.data.qrCode) {
                        console.log('QR data payload (pharma-admin):', json.data.qrData || {});
                        var qrHtml = '<div class="flex flex-col items-center">' +
                            '<img id="doctor-qr-img" src="' + json.data.qrCode + '" alt="Doctor QR Code" class="w-40 h-auto max-w-full border border-gray-300 dark:border-gray-600 rounded" />' +
                            '<a id="download-qr-btn" href="' + json.data.qrCode + '" download="doctor_qr_' + doctorId + '.png" class="mt-2 px-3 py-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-100 dark:hover:bg-gray-200 text-black dark:text-black border border-gray-400 rounded text-xs">Download QR</a>' +
                            '</div>';
                        qrContainer.innerHTML = qrHtml;

                        var downloadBtn = document.getElementById('download-qr-btn');
                        if (downloadBtn) {
                            downloadBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                var href = this.href;
                                fetch(href).then(function(res){ return res.blob(); }).then(function(blob){
                                    var url = URL.createObjectURL(blob);
                                    var a = document.createElement('a');
                                    a.href = url;
                                    a.download = 'doctor_qr_' + doctorId + '.png';
                                    document.body.appendChild(a);
                                    a.click();
                                    a.remove();
                                    URL.revokeObjectURL(url);
                                }).catch(function(){
                                    window.location = href;
                                });
                            });
                        }
                    } else {
                        console.warn('QR API returned unsuccessful status or missing data (pharma-admin):', json);
                        qrContainer.innerHTML = '<p class="text-xs text-red-500">Failed to load QR code.</p>';
                    }
                })
                .catch(function(err) {
                    console.error('QR fetch error (pharma-admin):', err);
                    qrContainer.innerHTML = '<p class="text-xs text-red-500">Error loading QR code.</p>';
                });
        });
    });
    </script>
</div>
@endsection
