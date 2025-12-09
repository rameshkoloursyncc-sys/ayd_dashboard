<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DoctorRepository
{
    /**
     * Create a new Doctor mapping record in the local database.
     * Only stores the relationship between doctor (via API ID), pharma company, and medical executive.
     *
     * @param array $data The data for the new doctor.
     * @param string $apiId The API ID from the external service.
     * @return Doctor
     */
    public function create(array $data, string $apiId): Doctor
    {
        $doctor = Doctor::create([
            'api_id' => $apiId,
            'pharma_company_id' => $data['pharma_company_id'],
            'medical_executive_id' => $data['medical_executive_id'] ?? null,
        ]);

        Log::info('Local doctor mapping record created.', ['doctor_id' => $doctor->id, 'api_id' => $apiId]);

        return $doctor;
    }
}
