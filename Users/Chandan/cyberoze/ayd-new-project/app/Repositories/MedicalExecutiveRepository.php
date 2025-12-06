<?php

namespace App\Repositories;

use App\Models\MedicalExecutive;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MedicalExecutiveRepository
{
    /**
     * Create a new MedicalExecutive record in the local database.
     *
     * @param array $data The data for the new medical executive.
     * @param User $user The associated user record.
     * @param string|null $apiId The API ID from the external service.
     * @return MedicalExecutive
     */
    public function create(array $data, User $user, ?string $apiId = null): MedicalExecutive
    {
        $executive = MedicalExecutive::create([
            'user_id' => $user->id,
            'pharma_company_id' => $data['pharma_company_id'],
            'api_id' => $apiId,
        ]);

        Log::info('Local medical executive record created.', ['medical_executive_id' => $executive->id, 'user_id' => $user->id, 'api_id' => $apiId]);

        return $executive;
    }
}
