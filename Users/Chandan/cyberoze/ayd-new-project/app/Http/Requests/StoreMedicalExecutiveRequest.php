<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreMedicalExecutiveRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $input = $this->all();
        // Robust mapping: Only map if not already a valid local DB ID
        if (!empty($input['pharma_company_id'])) {
            $isLocalId = \App\Models\PharmaCompany::where('id', $input['pharma_company_id'])->exists();
            if (!$isLocalId) {
                $localPharma = \App\Models\PharmaCompany::where('api_id', $input['pharma_company_id'])->first();
                if ($localPharma) {
                    $this->merge(['pharma_company_id' => $localPharma->id]);
                }
            }
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile_no' => 'required|string|max:20',
            'age' => 'nullable|integer|min:18|max:100', // Assuming reasonable age limits
            'gender' => 'nullable|string|in:Male,Female,Other',
            'employeeNo' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'whatsappNo' => 'nullable|string|max:20',
        ];

        if (Auth::user()->role === 'super_admin') {
            $rules['pharma_company_id'] = 'required|exists:pharma_companies,id';
        }

        return $rules;
    }
}
