@extends('layouts.app')

@section('content')
<div class="px-4 pt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Doctors</h1>
        <a href="{{ route('superadmin.doctors.create') }}" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Create New</a>
    </div>

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Pharma Company</th>
                        <th scope="col" class="px-6 py-3">Medical Executive</th>
                        <th scope="col" class="px-6 py-3">Approval Status</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $doctor->name }}</td>
                            <td class="px-6 py-4">{{ $doctor->email }}</td>
                            <td class="px-6 py-4">
                                {{ $doctor->pharmaCompanyName ?? 'N/A' }}
                                @if(empty($doctor->pharmaCompanyName) || $doctor->pharmaCompanyName == 'N/A')
                                    <!-- Attach to Pharma Button/Dropdown -->
                                    <form action="{{ route('superadmin.doctors.attachPharma', $doctor->api_id) }}" method="POST" class="inline-flex items-center mt-2">
                                        @csrf
                                        <select name="pharma_company_id" class="mr-2 rounded border-gray-300">
                                            <option value="">Select Pharma</option>
                                            @foreach($localPharmaCompanies as $pharma)
                                                <option value="{{ $pharma->api_id }}">{{ $pharma->user->name ?? $pharma->api_id }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 text-xs">Attach</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $doctor->medicalExecutiveName ?? 'N/A' }}</td>
                            <td class="px-6 py-4 approval-status" data-api-id="{{ $doctor->api_id }}">{{ $doctor->approvalStatus ?? 'N/A' }}</td>
                            <td class="p-4 space-x-2 whitespace-nowrap">
                                <a href="{{ route('superadmin.doctors.show', $doctor->api_id) }}" class="inline-flex items-center p-2 text-sm font-medium text-center text-white rounded-lg bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z" />
                                    </svg>
                                </a>
                                <a href="{{ route('superadmin.doctors.edit', $doctor->api_id) }}" class="inline-flex items-center p-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form action="{{ route('superadmin.doctors.destroy', $doctor->api_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this doctor?');" class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center p-2 text-sm font-medium text-center text-white rounded-lg bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                                @if(!$doctor->is_local)
                                    <button type="button" class="inline-flex items-center p-2 text-sm font-medium text-center text-white rounded-lg bg-yellow-600 hover:bg-yellow-700 focus:ring-4 focus:ring-yellow-300 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800" onclick="openAttachPharmaModal('{{ $doctor->api_id }}')">
                                        Attach Pharma
                                    </button>
                                @endif
                                @php
                                    $status = isset($doctor->approvalStatus) ? strtolower($doctor->approvalStatus) : null;
                                @endphp
                                @if($status !== 'approved')
                                    <button type="button" data-api-id="{{ $doctor->api_id }}" class="approve-btn inline-flex items-center p-2 text-sm font-medium text-center text-white rounded-lg bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800">Approve</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">No doctors found.</td>
                        </tr>
                    @endforelse
                    @push('modals')
                    <!-- Attach Pharma Modal (single instance) -->
                    <div id="attachPharmaModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold mb-4">Attach Doctor to Pharma Company</h2>
                            <form id="attachPharmaForm" method="POST" action="">
                                @csrf
                                <input type="hidden" name="doctor_api_id" id="modal_doctor_api_id" value="">
                                <label for="modal_pharma_company_id" class="block mb-2 text-sm font-medium text-gray-900">Select Pharma Company</label>
                                <select name="pharma_company_id" id="modal_pharma_company_id" class="w-full mb-4 p-2 border rounded">
                                    <option value="">Select Pharma</option>
                                    @foreach($localPharmaCompanies as $pharma)
                                        <option value="{{ $pharma->api_id }}">{{ $pharma->user->name ?? $pharma->api_id }}</option>
                                    @endforeach
                                </select>
                                <label for="modal_medical_executive_id" class="block mb-2 text-sm font-medium text-gray-900">Assign Medical Executive (Optional)</label>
                                <select name="medical_executive_id" id="modal_medical_executive_id" class="w-full mb-4 p-2 border rounded">
                                    <option value="">Do not assign</option>
                                </select>

                                <div class="flex justify-end">
                                    <button type="button" onclick="closeAttachPharmaModal()" class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Attach</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <script>
                        const AYD_CSRF_TOKEN = '{{ csrf_token() }}';
                        function showSwal(type, title, text) {
                            if (window.Swal) {
                                if (type === 'success') {
                                    Swal.fire({icon: 'success', title: title, text: text, timer: 2000});
                                } else {
                                    Swal.fire({icon: 'error', title: title, text: text});
                                }
                            } else {
                                alert(text);
                            }
                        }

                        // Approve button AJAX handler
                        document.addEventListener('DOMContentLoaded', function() {
                            document.querySelectorAll('.approve-btn').forEach(function(btn) {
                                btn.addEventListener('click', function(e) {
                                    var apiId = btn.getAttribute('data-api-id');
                                    if (!confirm('Approve this doctor and set approvalStatus to Approved?')) return;
                                    var url = '/superadmin/doctors/' + apiId;
                                    var body = new URLSearchParams();
                                    body.append('approvalStatus', 'Approved');
                                    fetch(url, {
                                        method: 'PUT',
                                        headers: {
                                            'X-CSRF-TOKEN': AYD_CSRF_TOKEN,
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/x-www-form-urlencoded'
                                        },
                                        body: body.toString()
                                    }).then(function(res) {
                                        if (!res.ok) throw res;
                                        return res.json();
                                    }).then(function(json) {
                                        if (json.success) {
                                            // update the approval status cell
                                            var cell = document.querySelector('.approval-status[data-api-id="' + apiId + '"]');
                                            if (cell) cell.textContent = 'Approved';
                                            // hide the approve button
                                            btn.style.display = 'none';
                                            showSwal('success', 'Approved', json.message || 'Doctor approved');
                                        } else {
                                            showSwal('error', 'Error', json.message || 'Failed to approve');
                                        }
                                    }).catch(function(err) {
                                        if (err.json) {
                                            err.json().then(function(j){
                                                showSwal('error', 'Error', j.message || 'Failed to approve');
                                            }).catch(function(){
                                                showSwal('error', 'Error', 'Failed to approve');
                                            });
                                        } else {
                                            showSwal('error', 'Error', 'Failed to approve');
                                        }
                                    });
                                });
                            });
                        });
                        window.openAttachPharmaModal = function(doctorApiId) {
                            var modal = document.getElementById('attachPharmaModal');
                            if (!modal) return;
                            modal.classList.remove('hidden');
                            var input = document.getElementById('modal_doctor_api_id');
                            if (input) input.value = doctorApiId;
                            var form = document.getElementById('attachPharmaForm');
                            if (form) form.action = '/superadmin/doctors/' + doctorApiId + '/attach-pharma';
                            // reset selects
                            var pharmaSel = document.getElementById('modal_pharma_company_id');
                            if (pharmaSel) pharmaSel.selectedIndex = 0;
                            var execSel = document.getElementById('modal_medical_executive_id');
                            if (execSel) execSel.innerHTML = '<option value="">Do not assign</option>';
                        }
                        window.closeAttachPharmaModal = function() {
                            var modal = document.getElementById('attachPharmaModal');
                            if (!modal) return;
                            modal.classList.add('hidden');
                            var input = document.getElementById('modal_doctor_api_id');
                            if (input) input.value = '';
                            var select = document.getElementById('modal_pharma_company_id');
                            if (select) select.selectedIndex = 0;
                            var execSel = document.getElementById('modal_medical_executive_id');
                            if (execSel) execSel.innerHTML = '<option value="">Do not assign</option>';
                            // Reset subscription fields
                            var subCheck = document.getElementById('subscribe_plan');
                            if (subCheck) {
                                subCheck.checked = false;
                                toggleSubscriptionFields(subCheck);
                            }
                        }

                        window.toggleSubscriptionFields = function(checkbox) {
                            const details = document.getElementById('subscription_details');
                            if (checkbox.checked) {
                                details.classList.remove('hidden');
                            } else {
                                details.classList.add('hidden');
                            }
                        }

                        // fetch medical executives for the selected pharma inside the modal
                        document.addEventListener('DOMContentLoaded', function() {
                            var modalPharma = document.getElementById('modal_pharma_company_id');
                            var modalExec = document.getElementById('modal_medical_executive_id');
                            if (!modalPharma || !modalExec) return;
                            modalPharma.addEventListener('change', function() {
                                var pharmaApiId = modalPharma.value;
                                modalExec.innerHTML = '<option value="">Loading...</option>';
                                if (!pharmaApiId) {
                                    modalExec.innerHTML = '<option value="">Do not assign</option>';
                                    return;
                                }
                                fetch('/superadmin/medical-executives/by-pharma/' + pharmaApiId)
                                    .then(response => response.json())
                                    .then(data => {
                                        let options = '<option value="">Do not assign</option>';
                                        if (Array.isArray(data.data)) {
                                            data.data.forEach(function(exec) {
                                                options += `<option value="${exec._id}">${exec.name}</option>`;
                                            });
                                        }
                                        modalExec.innerHTML = options;
                                    })
                                    .catch(() => {
                                        modalExec.innerHTML = '<option value="">Do not assign</option>';
                                    });
                            });
                        });
                    </script>
                    @endpush
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
