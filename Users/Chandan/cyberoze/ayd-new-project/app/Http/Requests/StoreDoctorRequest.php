<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDoctorRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $input = $this->all();
        // Robust mapping: Only map if not already a valid local DB ID
        if (!empty($input['pharma_company_id'])) {
            // If it's already a valid local ID, skip mapping
            $isLocalId = \App\Models\PharmaCompany::where('id', $input['pharma_company_id'])->exists();
            if (!$isLocalId) {
                $localPharma = \App\Models\PharmaCompany::where('api_id', $input['pharma_company_id'])->first();
                if ($localPharma) {
                    $this->merge(['pharma_company_id' => $localPharma->id]);
                }
            }
        }
        if (!empty($input['medical_executive_id'])) {
            $isLocalId = \App\Models\MedicalExecutive::where('id', $input['medical_executive_id'])->exists();
            if (!$isLocalId) {
                $localExec = \App\Models\MedicalExecutive::where('api_id', $input['medical_executive_id'])->first();
                if ($localExec) {
                    $this->merge(['medical_executive_id' => $localExec->id]);
                }
            }
        }
    }
        protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
        {
            \Log::error('[StoreDoctorRequest] Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $this->all(),
                'user_id' => \Auth::id(),
                'role' => optional(\Auth::user())->role
            ]);
            parent::failedValidation($validator);
        }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        \Log::info('[StoreDoctorRequest] authorize called', [
            'user_id' => Auth::id(),
            'role' => optional(Auth::user())->role
        ]);
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        \Log::info('[StoreDoctorRequest] rules called', [
            'user_id' => Auth::id(),
            'role' => optional(Auth::user())->role,
            'input' => $this->all()
        ]);
        $rules = [
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'gender' => 'required|string',
            'dob' => 'nullable|date',
            'age' => 'nullable|integer|min:0',
            'degree' => 'nullable|string|max:100',
            'placeName' => 'required|string|max:255',
            'registrationNo' => 'required|string|max:100',
            'yearOfRegistration' => 'nullable|string|max:10',
            'city' => 'required|string|max:100',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'string',
            'email' => 'nullable|email',
            'password' => 'nullable|string',
        ];

        $user = Auth::user();
        if ($user && $user->role === 'super_admin') {
            $rules['pharma_company_id'] = 'required|exists:pharma_companies,id';
            $rules['medical_executive_id'] = 'nullable|exists:medical_executives,id';
        } elseif ($user && $user->role === 'pharma_admin') {
            $rules['medical_executive_id'] = 'nullable|exists:medical_executives,id';
        }

        return $rules;
    }
}