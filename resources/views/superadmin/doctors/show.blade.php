@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Doctor Details</h1>
        <a href="{{ route('superadmin.doctors.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Back to list</a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
            <span class="font-medium">Success!</span> {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
            <span class="font-medium">Error!</span> 
            <ul class="mt-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php $status = isset($doctor->approvalStatus) ? strtolower($doctor->approvalStatus) : null; @endphp
    @if(Auth::user()->role === 'super_admin' && $status !== 'approved')
        <div class="mb-4">
            <button id="approve-doctor-btn" data-api-id="{{ $doctor->api_id }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Approve Doctor</button>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('approve-doctor-btn');
                if (!btn) return;
                btn.addEventListener('click', function() {
                    if (!confirm('Approve this doctor and set approvalStatus to Approved?')) return;
                    var apiId = btn.getAttribute('data-api-id');
                    var url = '/superadmin/doctors/' + apiId;
                    var body = new URLSearchParams();
                    body.append('approvalStatus', 'Approved');
                    fetch(url, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: body.toString()
                    }).then(function(res){
                        if (!res.ok) throw res;
                        return res.json();
                    }).then(function(json){
                        if (json.success) {
                            btn.style.display = 'none';
                            if (window.Swal) Swal.fire({icon:'success', title:'Approved', text: json.message || 'Doctor approved', timer:2000});
                            // update any status badge on page
                            var badge = document.querySelector('.text-gray-900');
                            if (badge) badge.textContent = 'Approved';
                        } else {
                            if (window.Swal) Swal.fire({icon:'error', title:'Error', text: json.message || 'Failed to approve'});
                        }
                    }).catch(function(err){
                        if (err.json) { err.json().then(function(j){ if (window.Swal) Swal.fire({icon:'error', title:'Error', text: j.message || 'Failed to approve'}); }).catch(function(){ if (window.Swal) Swal.fire({icon:'error', title:'Error', text: 'Failed to approve'}); }); }
                        else { if (window.Swal) Swal.fire({icon:'error', title:'Error', text: 'Failed to approve'}); }
                    });
                });
            });
        </script>
    @endif

    <div class="grid grid-cols-1 gap-6">
        <!-- Doctor Details -->
        <div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->email ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mobile Number</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->phone ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date of Birth</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->dob ? \Carbon\Carbon::parse($doctor->dob)->format('d M Y') : 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Age</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->age ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gender</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->gender ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Degree</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->degree ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Place Name</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->placeName ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Registration No.</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->registrationNo ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Year of Registration</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->yearOfRegistration ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Service</label>
                        <p class="text-gray-900 dark:text-white">{{ (isset($doctor->service_names) && count($doctor->service_names) > 0) ? implode(', ', $doctor->service_names) : ($doctor->service_id ?? 'N/A') }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pharma Company</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->pharmaCompanyName ?? 'N/A' }}</p>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Medical Executive</label>
                        <p class="text-gray-900 dark:text-white">{{ $doctor->medicalExecutiveName ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Section (simple, on-demand) -->
    <div class="mt-6 p-4 bg-white border border-dashed border-gray-300 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
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
                console.error('No doctorId found on load-qr-btn');
                return;
            }

            console.log('Fetching QR for doctorId:', doctorId);
            qrContainer.innerHTML = '<svg class="animate-spin h-6 w-6 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            fetch('https://ayd.pinktreehealth.com/api/qr/' + doctorId)
                .then(function(res) {
                    console.log('QR API raw response status:', res.status);
                    if (!res.ok) throw new Error('API request failed');
                    return res.json();
                })
                .then(function(json) {
                    console.log('QR API parsed JSON:', json);
                    if (json.status && json.data && json.data.qrCode) {
                        console.log('QR data payload:', json.data.qrData || {});
                        var qrHtml = '<div class="flex flex-col items-center">' +
                            '<img id="doctor-qr-img" src="' + json.data.qrCode + '" alt="Doctor QR Code" class="w-40 h-auto max-w-full border border-gray-300 dark:border-gray-600 rounded" />' +
                            '<a id="download-qr-btn" href="' + json.data.qrCode + '" download="doctor_qr_' + doctorId + '.png" class="mt-2 px-3 py-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-100 dark:hover:bg-gray-200 text-black dark:text-black border border-gray-400 rounded text-xs">Download QR</a>' +
                            '</div>';
                        qrContainer.innerHTML = qrHtml;

                        // Attach fallback download handler to force blob download when possible
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
                                    // If fetch fails (CORS), fall back to normal navigation
                                    window.location = href;
                                });
                            });
                        }
                    } else {
                        console.warn('QR API returned unsuccessful status or missing data:', json);
                        qrContainer.innerHTML = '<p class="text-xs text-red-500">Failed to load QR code.</p>';
                    }
                })
                .catch(function(err) {
                    console.error('QR fetch error:', err);
                    qrContainer.innerHTML = '<p class="text-xs text-red-500">Error loading QR code.</p>';
                });
        });
    });
    </script>

    <!-- Subscription Section -->
    <div class="mt-6 p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <!-- Wallet & Payouts Section -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Wallet & Payouts</h2>
            <button type="button" data-modal-target="mark-payout-modal" data-modal-toggle="mark-payout-modal" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
                Mark as Paid
            </button>
        </div>

        @if(isset($walletSummary))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Earnings</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">₹{{ number_format($walletSummary['totalEarnings'] ?? 0, 2) }}</div>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paid</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">₹{{ number_format($walletSummary['totalPaid'] ?? 0, 2) }}</div>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Balance Due</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">₹{{ number_format($walletSummary['balance'] ?? 0, 2) }}</div>
            </div>
        </div>
        @endif

        <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mb-2">Payout History</h3>
        @if(isset($payoutHistory) && count($payoutHistory) > 0)
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-6">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Date</th>
                        <th scope="col" class="px-6 py-3">Month</th>
                        <th scope="col" class="px-6 py-3">Amount</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payoutHistory as $payout)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($payout['payoutDate'] ?? $payout['createdAt'])->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ $payout['payoutMonth'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4">₹{{ number_format($payout['amount'] ?? 0, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                {{ ucfirst($payout['status'] ?? 'paid') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">No payout history found.</p>
        @endif

        <!-- Bank Account Details -->
        <div class="flex justify-between items-center mt-6 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Bank Account Details</h2>
            <button type="button" data-modal-target="add-bank-account-modal" data-modal-toggle="add-bank-account-modal" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                Add Bank Account
            </button>
        </div>
        
        @php
            $displayAccounts = [];
            if (isset($accounts) && is_array($accounts) && count($accounts) > 0) {
                $displayAccounts = $accounts;
            } elseif (isset($doctor->bankAccounts) && is_array($doctor->bankAccounts) && count($doctor->bankAccounts) > 0) {
                $displayAccounts = $doctor->bankAccounts;
            }
        @endphp

        @if(count($displayAccounts) > 0)
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-6">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Account No</th>
                        <th scope="col" class="px-6 py-3">IFSC</th>
                        <th scope="col" class="px-6 py-3">PAN</th>
                        <th scope="col" class="px-6 py-3">Aadhaar</th>
                        <th scope="col" class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayAccounts as $account)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4">{{ $account['accountno'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $account['ifsc'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $account['pan'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $account['addaar'] ?? $account['aadhaar'] ?? $account['adhaar_no'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <button type="button" class="font-medium text-blue-600 dark:text-blue-400 hover:underline ml-2 edit-account-btn" data-account='@json($account)'>Edit</button>
                        </td>
                    </tr>

                            {{-- Only include the modal once, outside the loop --}}
                            @include('superadmin.doctors.edit-account-modal')

                            @push('scripts')
                            <script>
                            function openEditAccountModal(account) {
                                document.getElementById('edit-account-modal').classList.remove('hidden');
                                document.getElementById('edit-doctorId').value = account.doctorId || '';
                                document.getElementById('edit-accountno').value = Array.isArray(account.accountno) ? '' : (account.accountno || '');
                                document.getElementById('edit-ifsc').value = Array.isArray(account.ifsc) ? '' : (account.ifsc || '');
                                document.getElementById('edit-pan').value = Array.isArray(account.pan) ? '' : (account.pan || '');
                                document.getElementById('edit-addaar').value = account.addaar || account.aadhaar || account.adhaar_no || '';
                                document.getElementById('edit-source').value = account.source || '';
                                // Set form action to match route: /superadmin/doctors/bank-account/{accountId}
                                var form = document.getElementById('edit-account-form');
                                var id = account._id || account.id;
                                form.action = id ? ('/superadmin/doctors/bank-account/' + id) : '';
                            }
                            function closeEditAccountModal() {
                                document.getElementById('edit-account-modal').classList.add('hidden');
                            }
                            document.addEventListener('DOMContentLoaded', function() {
                                document.querySelectorAll('.edit-account-btn').forEach(function(btn) {
                                    btn.addEventListener('click', function() {
                                        var account = btn.getAttribute('data-account');
                                        if (account) {
                                            openEditAccountModal(JSON.parse(account));
                                        }
                                    });
                                });
                            });
                            </script>
                            @endpush
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">No bank accounts found.</p>
        @endif

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h2>
        
        @php
            $apiSuccess = isset($planDetails) && !empty($planDetails) && isset($planDetails['status']) && $planDetails['status'] == true;
            $dataRaw = $apiSuccess && isset($planDetails['data']) ? $planDetails['data'] : null;
            $activePlan = null;
            
            // Handle potentially nested plan structure (data.plan vs data)
            if ($dataRaw) {
                if (isset($dataRaw['plan']) && is_array($dataRaw['plan'])) {
                    $activePlan = $dataRaw['plan'];
                } else {
                    $activePlan = $dataRaw;
                }
            }

            // Determine if plan is logically active
            $isPlanActive = false;
            if ($activePlan && isset($activePlan['planStatus']) && strtolower($activePlan['planStatus']) === 'active') {
                $isPlanActive = true;
            }
        @endphp
        
        @if($isPlanActive)
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-gray-700 dark:border-green-600">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-green-800 dark:text-green-300">Active Subscription</h3>
                </div>
                
                @if(isset($activePlan))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
                        @if(isset($activePlan['planName']))
                        <div><span class="font-semibold">Plan Name:</span> {{ $activePlan['planName'] }}</div>
                        @endif
                        @if(isset($activePlan['startDate']))
                        <div><span class="font-semibold">Start Date:</span> {{ \Carbon\Carbon::parse($activePlan['startDate'])->format('d M Y') }}</div>
                        @endif
                        @if(isset($activePlan['endDate']) || isset($activePlan['expireDate']))
                        <div><span class="font-semibold">End Date:</span> {{ \Carbon\Carbon::parse($activePlan['endDate'] ?? $activePlan['expireDate'])->format('d M Y') }}</div>
                        @endif
                        @if(isset($activePlan['amount']))
                        <div><span class="font-semibold">Amount:</span> ₹{{ $activePlan['amount'] }}</div>
                        @endif
                        @if(isset($activePlan['validity']))
                        <div><span class="font-semibold">Validity:</span> {{ $activePlan['validity'] }} Years</div>
                        @elseif(isset($activePlan['startDate']) && (isset($activePlan['endDate']) || isset($activePlan['expireDate'])))
                            @php
                                $start = \Carbon\Carbon::parse($activePlan['startDate']);
                                $end = \Carbon\Carbon::parse($activePlan['endDate'] ?? $activePlan['expireDate']);
                                $diffInYears = $start->diffInYears($end);
                            @endphp
                            @if($diffInYears > 0)
                            <div><span class="font-semibold">Validity:</span> {{ $diffInYears }} Years</div>
                            @endif
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">Plan is active.</p>
                @endif
            </div>
        @else
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg dark:bg-gray-700 dark:border-yellow-600">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-300">No Active Subscription</h3>
                </div>
                <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-400">This doctor does not have an active subscription plan.</p>
            </div>
        @endif

        @php
            $defaultYears = '1';
            if ($isPlanActive && $activePlan) {
                if (isset($activePlan['validity'])) {
                    $defaultYears = $activePlan['validity'];
                } elseif (isset($activePlan['startDate']) && (isset($activePlan['endDate']) || isset($activePlan['expireDate']))) {
                    $start = \Carbon\Carbon::parse($activePlan['startDate']);
                    $end = \Carbon\Carbon::parse($activePlan['endDate'] ?? $activePlan['expireDate']);
                    $diff = $start->diffInYears($end);
                    if ($diff > 0) $defaultYears = $diff;
                }
            }
        @endphp

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $isPlanActive ? 'Update Subscription' : 'Assign Subscription' }}</h2>
        
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-gray-700 dark:border-blue-600">
            <h3 class="font-medium text-blue-800 dark:text-blue-300 mb-2">Plan Details: Standard Annual Plan</h3>
            <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                <li>Base Price: ₹999</li>
                <li>GST (18%): ₹180</li>
                <li class="font-bold mt-1">Total Payable: ₹1179</li>
            </ul>
        </div>

        <form action="{{ route('superadmin.doctors.subscribe', $doctor->api_id) }}" method="POST">
            @csrf
            @if($isPlanActive && $activePlan && isset($activePlan['_id']))
                <input type="hidden" name="planId" value="{{ $activePlan['_id'] }}">
                <div class="mb-2 text-xs text-gray-500">Updating Plan ID: {{ $activePlan['_id'] }}</div>
            @endif
            <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                    <label for="amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Amount (999 + 18% GST)</label>
                    <input type="number" name="amount" id="amount" value="1179" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed" readonly>
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label for="years" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration (Years)</label>
                    <input type="number" name="years" id="years" value="1" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed" readonly>
                </div>
                <div class="col-span-6">
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">{{ $isPlanActive ? 'Update Subscription' : 'Assign Subscription' }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

<!-- Add Bank Account Modal -->
<div id="add-bank-account-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="add-bank-account-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="px-6 py-6 lg:px-8">
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add Bank Account</h3>
                <form class="space-y-6" action="{{ route('superadmin.doctors.storeBankAccount') }}" method="POST">
                    @csrf
                    <input type="hidden" name="doctorId" value="{{ $doctor->api_id }}">
                    <input type="hidden" name="source" value="admins">
                    
                    <div>
                        <label for="accountno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Account Number</label>
                        <input type="text" name="accountno" id="accountno" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div>
                        <label for="ifsc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">IFSC Code</label>
                        <input type="text" name="ifsc" id="ifsc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div>
                        <label for="pan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PAN Number</label>
                        <input type="text" name="pan" id="pan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div>
                        <label for="addaar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Aadhaar Number</label>
                        <input type="text" name="addaar" id="addaar" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div>
                        <label for="otherDocument" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Other Document (Optional)</label>
                        <input type="text" name="otherDocument" id="otherDocument" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Add Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Mark Payout Modal -->
<div id="mark-payout-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="mark-payout-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="px-6 py-6 lg:px-8">
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Mark Payout as Paid</h3>
                <form class="space-y-6" action="{{ route('superadmin.doctors.storePayout', $doctor->api_id) }}" method="POST">
                    @csrf
                    
                    <div>
                        <label for="payoutAmount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Payout Amount</label>
                        <input type="number" step="0.01" name="payoutAmount" id="payoutAmount" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div>
                        <label for="payoutMonth" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Payout Month</label>
                        <input type="month" name="payoutMonth" id="payoutMonth" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div>
                        <label for="payoutEndDate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Payout End Date</label>
                        <input type="date" name="payoutEndDate" id="payoutEndDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    
                    <button type="submit" class="w-full text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Mark as Paid</button>
                </form>
            </div>
        </div>
    </div>
</div>
