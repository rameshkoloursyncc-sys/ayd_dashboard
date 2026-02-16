<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\MedicalExecutiveRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class MedicalExecutiveCreationService
{
    protected $pinktreeApiService;
    protected $medicalExecutiveRepository;

    public function __construct(PinktreeApiService $pinktreeApiService, MedicalExecutiveRepository $medicalExecutiveRepository)
    {
        $this->pinktreeApiService = $pinktreeApiService;
        $this->medicalExecutiveRepository = $medicalExecutiveRepository;
    }

    /**
     * Orchestrates the creation of a Medical Executive.
     *
     * @param array $data Validated request data.
     * @return \App\Models\MedicalExecutive
     * @throws \Exception
     */
    public function create(array $data)
    {
        // Only local fields for User
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'medical_executive',
            'pharma_company_id' => $data['pharma_company_id'],
            'created_by' => Auth::id(),
            'mobile_no' => $data['mobile_no'],
        ]);

        Log::info('Local user created for medical executive.', ['user_id' => $user->id]);

        try {
            // Get the API ID of the Pharma Company
            $pharmaCompany = \App\Models\PharmaCompany::find($data['pharma_company_id']);
            if (!$pharmaCompany) {
                throw new Exception('Pharma Company not found locally.');
            }
            $pharmaCompanyApiId = $pharmaCompany->api_id;

            // Prepare API data (all fields needed by API)
            $apiData = [
                'pharma_company_api_id' => $pharmaCompanyApiId,
                'name' => $data['name'],
                'age' => $data['age'] ?? null,
                'gender' => $data['gender'] ?? null,
                'employeeNo' => $data['employeeNo'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'mobile_no' => $data['mobile_no'] ?? null,
                'whatsappNo' => $data['whatsappNo'] ?? null,
                'email' => $data['email'],
            ];

            // Step 2: Call the external API to create the medical practitioner.
            $response = $this->pinktreeApiService->createMedicalExecutive($apiData);

            if ($response->failed()) {
                throw new Exception('Failed to create medical executive on the external platform. API Error: ' . $response->body());
            }

            Log::info('Successfully created medical executive on Pinktree API.');

            $apiResponse = $response->json();
            $apiId = $apiResponse['data']['_id'] ?? null;

            if (!$apiId) {
                throw new Exception('Pinktree API did not return an _id for the created medical executive.');
            }

            // Only local fields for MedicalExecutive
            $localData = [
                'pharma_company_id' => $data['pharma_company_id'],
            ];
            $localExecutive = $this->medicalExecutiveRepository->create($localData, $user, $apiId);

            return $localExecutive;

        } catch (\Exception $e) {
            $user->delete();
            Log::error('Medical executive creation process failed and user was rolled back.', [
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}