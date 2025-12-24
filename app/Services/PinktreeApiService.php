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

    public function getCDoctorInfo(string $doctorId)
    {
        return Http::post($this->baseUrl . '/api/getCDoctorInfo', [
            'doctorId' => $doctorId,
        ]);
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
        return Http::post($this->baseUrl . '/api/plan/subscribe', $data);
    }

    public function checkDoctorPlan(string $doctorId)
    {
        return Http::post($this->baseUrl . '/api/plan/checkDoctorPlan', [
            'doctorID' => $doctorId,
        ]);
    }
}