<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PinktreeApiService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('PINKTREE_API_BASE_URL');
        if (!$this->baseUrl) {
            throw new Exception('PINKTREE_API_BASE_URL is not set in environment.');
        }
    }

    /**
     * Centralized logging for API requests made by this service.
     */
    private function logHttpCall(string $method, string $url, $payload, $response)
    {
        try {
            $status = method_exists($response, 'status') ? $response->status() : null;
            $success = method_exists($response, 'successful') ? $response->successful() : null;
            $body = method_exists($response, 'body') ? $response->body() : null;
            $headers = method_exists($response, 'headers') ? $response->headers() : null;

            Log::info('[PinktreeAPI] HTTP Call', [
                'method' => $method,
                'url' => $url,
                'payload' => $payload,
                'status' => $status,
                'success' => $success,
                'response_headers' => $headers,
                // Trim response body to avoid huge logs
                'response_body_snippet' => is_string($body) ? substr($body, 0, 2000) : $body,
            ]);
        } catch (Exception $e) {
            // Ensure logging never throws
            Log::error('[PinktreeAPI] Failed to log HTTP call', ['error' => $e->getMessage()]);
        }
    }

    // Pharma Company Endpoints
    public function getAllPharma()
    {
        return Http::get($this->baseUrl . '/api/getAllPharma');
    }

    public function createPharma(array $data)
    {
        return Http::post($this->baseUrl . '/api/createPharma', $data);
    }

    public function getByPharmaId(string $apiId)
    {
        return Http::get($this->baseUrl . '/api/getByPharmaId/' . $apiId);
    }

    public function deletePharma(string $apiId)
    {
        return Http::delete($this->baseUrl . '/api/deletePharma/' . $apiId);
    }

    // Medical Executive Endpoints
    public function createMedicalExecutive(array $data)
    {
        return Http::post($this->baseUrl . '/api/createMedicalPractitioner', [
            'pharmaConnectedTo' => $data['pharma_company_api_id'], // API expects _id of pharma company
            'name' => $data['name'],
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? null,
            'employeeNo' => $data['employeeNo'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'mobileNo' => $data['mobile_no'] ?? null,
            'whatsappNo' => $data['whatsappNo'] ?? null,
            'emailId' => $data['email'],
        ]);
    }

    public function updateMedicalExecutive(string $apiId, array $data)
    {
        return Http::post($this->baseUrl . '/api/createMedicalPractitioner', array_merge(['_id' => $apiId], $data));
    }

    public function getMedicalPractitionerByPharma(string $pharmaApiId)
    {
        return Http::get($this->baseUrl . '/api/getMedicalPractitionerByPharma/' . $pharmaApiId);
    }

    public function deleteMedicalPractitioner(string $medicalPractitionerId)
    {
        return Http::delete($this->baseUrl . '/api/deleteMedicalPractitioner/' . $medicalPractitionerId);
    }

    public function getAllMedicalPractitioners()
    {
        return Http::get($this->baseUrl . '/api/getAllMedicalPractitioners');
    }

    // Doctor Endpoints
    public function createDoctor(array $data)
    {
        return Http::post($this->baseUrl . '/api/createCDoctor', [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'gender' => $data['gender'],
            'dob' => $data['dob'] ?? null,
            'age' => $data['age'] ?? null,
            'degree' => $data['degree'] ?? null,
            'placeName' => $data['placeName'] ?? null,
            'registrationNo' => $data['registrationNo'] ?? null,
            'yearOfRegistration' => $data['yearOfRegistration'] ?? null,
            'email' => $data['email'] ?? null,
            'service_ids' => $data['service_ids'] ?? [],
            // Remove pharmaCompany and medicalExecutive, as per your note
        ]);
    }

    public function updateDoctor(string $apiId, array $data)
    {
        return Http::post($this->baseUrl . '/api/createCDoctor', array_merge(['_id' => $apiId], $data));
    }

    public function deleteDoctor(string $apiId)
    {
        return Http::delete($this->baseUrl . '/api/deleteDoctor/' . $apiId);
    }

    // Bank Account Endpoints
    public function createAccount(array $data)
    {
        $url = $this->baseUrl . '/api/createAccount';
        // Remove 'otherDocument' if not set or null
        if (!isset($data['otherDocument']) || $data['otherDocument'] === null) {
            unset($data['otherDocument']);
        } else if (!is_array($data['otherDocument'])) {
            // If present but not array, wrap as array
            $data['otherDocument'] = [$data['otherDocument']];
        }
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($url, $data);
        $this->logHttpCall('POST', $url, $data, $response);
        return $response;
    }

    public function updateAccount(array $data)
    {
        $url = $this->baseUrl . '/api/createAccount';
        // Remove 'otherDocument' if not set or null
        if (!isset($data['otherDocument']) || $data['otherDocument'] === null) {
            unset($data['otherDocument']);
        } else if (!is_array($data['otherDocument'])) {
            // If present but not array, wrap as array
            $data['otherDocument'] = [$data['otherDocument']];
        }
        // _id must be present for update
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($url, $data);
        $this->logHttpCall('POST', $url, $data, $response);
        return $response;
    }

    public function deleteAccount(string $accountId)
    {
        return Http::delete($this->baseUrl . '/api/deleteAccount/' . $accountId);
    }

    public function getCDoctorInfo(string $doctorId)
    {
        Log::info('[PinktreeAPI] Fetching doctor details from external API', [
            'doctorId' => $doctorId,
            'endpoint' => $this->baseUrl . '/api/getCDoctorInfo'
        ]);
        
        $url = $this->baseUrl . '/api/getCDoctorInfo';
        $payload = ['doctorId' => $doctorId];
        $response = Http::post($url, $payload);

        // Log request/response details
        $this->logHttpCall('POST', $url, $payload, $response);
        
        Log::info('[PinktreeAPI] Doctor details fetch response', [
            'doctorId' => $doctorId,
            'status' => method_exists($response, 'status') ? $response->status() : null,
            'success' => method_exists($response, 'successful') ? $response->successful() : null,
            'response_json' => $response->successful() ? $response->json() : null,
            'has_bank_fields' => [
                'accountno' => isset($response->json('data')['accountno']) ? 'present' : 'missing',
                'ifsc' => isset($response->json('data')['ifsc']) ? 'present' : 'missing',
                'pan' => isset($response->json('data')['pan']) ? 'present' : 'missing',
                'addaar' => isset($response->json('data')['addaar']) ? 'present' : 'missing',
            ]
        ]);
        
        return $response;
    }

    /**
     * Fetch doctor bank accounts by doctor API id using Pinktree endpoint
     * Returns array of account objects (or empty array on failure)
     */
    public function getDoctorAccountsById(string $doctorId)
    {
        try {
            $url = $this->baseUrl . '/api/listDoctorAccountsById/' . $doctorId;
            $response = Http::get($url);

            // centralized logging
            $this->logHttpCall('GET', $url, null, $response);

            if ($response->successful()) {
                $payload = $response->json();
                Log::info('[PinktreeAPI] listDoctorAccountsById success', ['doctorId' => $doctorId, 'count' => is_array($payload['data'] ?? null) ? count($payload['data']) : 0]);
                return $payload['data'] ?? [];
            }

            Log::error('[PinktreeAPI] listDoctorAccountsById failed', [
                'doctorId' => $doctorId,
                'status' => method_exists($response, 'status') ? $response->status() : null,
                'body' => method_exists($response, 'body') ? $response->body() : null,
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('[PinktreeAPI] Exception in listDoctorAccountsById', [
                'doctorId' => $doctorId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    // Services Endpoints
    public function listServices()
    {
        return Http::get($this->baseUrl . '/api/listServices');
    }

    public function createService(array $data)
    {
        return Http::post($this->baseUrl . '/api/createService', $data);
    }

    public function updateService(string $id, array $data)
    {
        return Http::post($this->baseUrl . '/api/createService', array_merge(['_id' => $id], $data));
    }

    public function deleteService(string $id)
    {
        return Http::delete($this->baseUrl . '/api/deleteService/' . $id);
    }

    // Banners Endpoints
    public function listAydBanner()
    {
        return Http::get($this->baseUrl . '/api/listAydBanner');
    }

    public function createAydBanner(array $data)
    {
        return Http::post($this->baseUrl . '/api/createAydBanner', $data);
    }

    public function updateAydBanner(string $id, array $data)
    {
        return Http::post($this->baseUrl . '/api/createAydBanner', array_merge(['_id' => $id], $data));
    }

    public function deleteAydBanner(string $id)
    {
        return Http::delete($this->baseUrl . '/api/deleteAydBanner/' . $id);
    }

    // Coupons Endpoints
    public function listAllChatCoupon()
    {
        return Http::get($this->baseUrl . '/api/listAllChatCoupon');
    }

    public function createChatCoupon(array $data)
    {
    return Http::post($this->baseUrl . '/api/createChatCoupon', $data);
    }

    public function updateChatCoupon(string $id, array $data)
    {
        return Http::post($this->baseUrl . '/api/createChatCoupon', array_merge(['_id' => $id], $data));
    }

    public function deleteChatCoupon(string $id)
    {
        return Http::delete($this->baseUrl . '/api/deleteChatCoupon/' . $id);
    }

    // FAQ Endpoints
    public function listAYDFaq()
    {
        return Http::get($this->baseUrl . '/api/listAYDFaq');
    }

    public function createAYDFaq(array $data)
    {
        return Http::post($this->baseUrl . '/api/createAYDFaq', $data);
    }

    public function updateAYDFaq(string $id, array $data)
    {
        return Http::post($this->baseUrl . '/api/createAYDFaq', array_merge(['_id' => $id], $data));
    }

    public function deleteAYDFaq(string $id)
    {
        return Http::delete($this->baseUrl . '/api/deleteAYDFaq/' . $id);
    }

    // Sponsors Endpoints
    public function listAydSponsor()
    {
        return Http::get($this->baseUrl . '/api/listAydSponsor');
    }

    public function createSponsor(array $data)
    {
        return Http::post($this->baseUrl . '/api/createSponsor', $data);
    }

    public function updateSponsor(string $id, array $data)
    {
        return Http::post($this->baseUrl . '/api/createSponsor', array_merge(['_id' => $id], $data));
    }

    public function deleteSponsor(string $id)
    {
        return Http::delete($this->baseUrl . '/api/deleteSponsor/' . $id);
    }

    // Patient Endpoints
    public function getAYDPatientListFromFilters(array $filters = [])
    {
        return Http::post($this->baseUrl . '/api/getAYDPatientListFromFilters', $filters);
    }

    // Quota Management
    public function incrementUsedActivationQuota(string $pharmaApiId, int $increment = 1)
    {
        // First, get the current pharma details to calculate new quota
        $response = $this->getByPharmaId($pharmaApiId);
        if ($response->failed()) {
            Log::error("Failed to fetch pharma details for quota increment: " . $response->body());
            return false;
        }

        $pharmaData = $response->json('data') ?? $response->json();
        $currentUsed = isset($pharmaData['usedActivationQuota']) ? (int)$pharmaData['usedActivationQuota'] : 0;
        $newUsed = $currentUsed + $increment;
        
        return Http::post($this->baseUrl . '/api/createPharma', [
            '_id' => $pharmaApiId,
            'usedActivationQuota' => $newUsed
        ]);
    }

    // Subscription Plan
    public function subscribePlan(array $data)
    {
        // Expected data: pharmaId, doctorId, amount, years, planId (optional)
        $url = $this->baseUrl . '/api/plan/subscribe';
        try {
            $response = Http::post($url, $data);
            // centralized logging
            $this->logHttpCall('POST', $url, $data, $response);
            return $response;
        } catch (Exception $e) {
            Log::error('[PinktreeAPI] subscribePlan exception', ['payload' => $data, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function checkDoctorPlan(string $doctorId)
    {
        $url = $this->baseUrl . '/api/plan/checkDoctorPlan';
        $data = ['doctorID' => $doctorId];
        $response = Http::post($url, $data);
        $this->logHttpCall('POST', $url, $data, $response);
        return $response;
    }

    // Payout Endpoints
    public function getWalletSummary(string $doctorId)
    {
        return Http::get($this->baseUrl . '/api/doctor/wallet/summary/' . $doctorId);
    }

    public function getPayoutHistory(string $doctorId)
    {
        return Http::get($this->baseUrl . '/api/doctor/payout-history/' . $doctorId);
    }

    public function createPayout(array $data)
    {
        // Ensure payload matches external API requirements
        $payload = [
            'doctorId' => $data['doctorId'] ?? $data['doctor_id'] ?? null,
            'payoutamount' => $data['payoutAmount'] ?? $data['payoutamount'] ?? null,
            'payoutMonth' => $data['payoutMonth'] ?? null,
            'payoutEndDate' => $data['payoutEndDate'] ?? null,
        ];
        // Remove nulls
        $payload = array_filter($payload, function($v) { return $v !== null; });
        return Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/api/doctor/payout', $payload);
    }
}