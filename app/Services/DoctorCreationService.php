<?php
namespace App\Services;
use App\Models\User;
use App\Models\PharmaCompany;
use App\Models\Doctor as LocalDoctor;
use App\Repositories\DoctorRepository;
use App\Services\WhatsAppService;
use App\Services\PinktreeApiService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class DoctorCreationService
{
    protected $doctorRepository;
    protected $whatsAppService;
    protected $pinktreeApiService;

    public function __construct(DoctorRepository $doctorRepository, WhatsAppService $whatsAppService, PinktreeApiService $pinktreeApiService)
    {
        $this->doctorRepository = $doctorRepository;
        $this->whatsAppService = $whatsAppService;
        $this->pinktreeApiService = $pinktreeApiService;
    }

    /**
     * Attempt to extract a friendly error message from an HTTP response object.
     * Falls back to raw body if no structured message is found.
     *
     * @param mixed $response
     * @return string
     */
    private function extractApiErrorMessage($response): string
    {
        try {
            if (method_exists($response, 'json')) {
                $json = $response->json();
                if (is_array($json)) {
                    if (!empty($json['message']) && is_string($json['message'])) {
                        return $json['message'];
                    }
                    if (!empty($json['error']) && is_string($json['error'])) {
                        return $json['error'];
                    }
                    if (!empty($json['errors'])) {
                        return is_string($json['errors']) ? $json['errors'] : json_encode($json['errors']);
                    }
                    if (!empty($json['data']) && is_array($json['data'])) {
                        // sometimes API returns nested message inside data
                        if (!empty($json['data']['message']) && is_string($json['data']['message'])) {
                            return $json['data']['message'];
                        }
                    }
                    // If nothing matched, return the whole json as string
                    return json_encode($json);
                }
            }
        } catch (\Exception $e) {
            // ignore and fallthrough to body
        }

        try {
            if (method_exists($response, 'body')) {
                return (string) $response->body();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return 'Unknown API error';
    }

        /**
     * Extract only the fields needed for Pinktree API doctor creation.
     */
    private function extractPinktreeDoctorPayload(array $data): array
    {
        // Always use 'mobile_no' from the form for Pinktree API as 'phone'
        $phone = $data['mobile_no'] ?? null;
        return [
            'name' => $data['name'],
            'phone' => $phone,
            'gender' => $data['gender'],
            'dob' => $data['dob'] ?? null,
            'age' => $data['age'] ?? null,
            'degree' => $data['degree'] ?? null,
            'placeName' => $data['placeName'] ?? null,
            'registrationNo' => $data['registrationNo'] ?? null,
            'yearOfRegistration' => $data['yearOfRegistration'] ?? null,
            'email' => $data['email'] ?? null,
            'service_ids' => $data['service_ids'] ?? [],
        ];
    }

    /**
     * Orchestrates the creation of a Doctor.
     *
     * @param array $data Validated request data.
     * @return \App\Models\Doctor
     * @throws \Exception
     */
    public function create(array $data)
    {
        try {
            Log::info('[DoctorCreation] Received data in create()', $data);
            // Step 1: Prepare and send only the required fields to Pinktree API
            $pinktreePayload = $this->extractPinktreeDoctorPayload($data);
            // Before calling Pinktree API, ensure pharma activation quota is not exceeded by fetching pharma details from API.
            $pharmaIdentifier = $data['pharma_company_id'] ?? null; // may be local id or API id depending on caller
            $localPharma = null;
            $pharmaApiId = null;
            if ($pharmaIdentifier) {
                if (is_numeric($pharmaIdentifier)) {
                    $localPharma = PharmaCompany::find($pharmaIdentifier);
                    if ($localPharma) {
                        $pharmaApiId = $localPharma->api_id;
                    }
                }
                if (!$pharmaApiId) {
                    $pharmaApiId = $pharmaIdentifier;
                    if (!$localPharma) {
                        $localPharma = PharmaCompany::where('api_id', $pharmaApiId)->first();
                    }
                }
            }

            $quota = null;
            if ($pharmaApiId) {
                try {
                    $pharmaResp = $this->pinktreeApiService->getByPharmaId($pharmaApiId);
                    if ($pharmaResp->successful()) {
                        $pharmaData = $pharmaResp->json('data') ?? $pharmaResp->json();
                        if (isset($pharmaData['totalActivationQuota'])) {
                            $quota = (int) $pharmaData['totalActivationQuota'];
                        }
                    } else {
                        throw new Exception('Failed to fetch pharma details for quota check: ' . $pharmaResp->body());
                    }
                } catch (\Exception $e) {
                    Log::error('[DoctorCreation] Could not fetch pharma data for quota check: ' . $e->getMessage());
                    throw new Exception('Could not verify pharma activation quota. Please try again later.');
                }
            }

            if (!is_null($quota) && $quota > 0) {
                $localPharmaId = $localPharma ? $localPharma->id : null;
                $currentCount = 0;
                if ($localPharmaId) {
                    $currentCount = LocalDoctor::where('pharma_company_id', $localPharmaId)->count();
                }
                // We're creating a new doctor, so this will increase count by 1
                if (($currentCount + 1) > $quota) {
                    throw new Exception('Pharma company activation quota exceeded. Cannot create more doctors for this company.');
                }
            }

            Log::info('[DoctorCreation] Sending data to Pinktree API for doctor creation', [
                'payload' => $pinktreePayload
            ]);
            $response = $this->pinktreeApiService->createDoctor($pinktreePayload);

            Log::info('[DoctorCreation] Pinktree API response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error('[DoctorCreation] Pinktree API doctor creation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $msg = $this->extractApiErrorMessage($response);
                throw new Exception('Failed to create doctor on external platform: ' . $msg);
            }

            $apiResponse = $response->json();
            $apiId = null;
            if (isset($apiResponse['data']['_id'])) {
                $apiId = $apiResponse['data']['_id'];
            } elseif (isset($apiResponse['data']) && isset($apiResponse['data']['_id'])) {
                $apiId = $apiResponse['data']['_id'];
            } elseif (isset($apiResponse['data']) && isset($apiResponse['data']['uniqueId'])) {
                // fallback if _id is not present, use uniqueId
                $apiId = $apiResponse['data']['uniqueId'];
            }
            if (!$apiId) {
                Log::error('[DoctorCreation] Pinktree API did not return an _id for the created doctor', [
                    'apiResponse' => $apiResponse
                ]);
                // Try to extract a friendly message from the API response
                $apiErrorMsg = null;
                if (is_array($apiResponse)) {
                    if (!empty($apiResponse['message'])) $apiErrorMsg = $apiResponse['message'];
                    elseif (!empty($apiResponse['error'])) $apiErrorMsg = $apiResponse['error'];
                    elseif (!empty($apiResponse['errors'])) $apiErrorMsg = json_encode($apiResponse['errors']);
                    else $apiErrorMsg = json_encode($apiResponse);
                }
                $apiErrorMsg = $apiErrorMsg ?? 'Pinktree API did not return an _id for the created doctor.';
                throw new Exception($apiErrorMsg);
            }

            Log::info('[DoctorCreation] Successfully created doctor on Pinktree API', [
                'api_id' => $apiId,
                'apiResponse' => $apiResponse
            ]);

            // Step 2: Always save pharma_company_id and medical_executive_id locally

            $localDoctor = $this->doctorRepository->create([
                'pharma_company_id' => $data['pharma_company_id'],
                'medical_executive_id' => $data['medical_executive_id'] ?? null,
            ], $apiId);

            // Increment Quota
            if ($pharmaApiId) {
                $this->pinktreeApiService->incrementUsedActivationQuota($pharmaApiId, 1);
            }

            // Subscription Plan
            if (!empty($data['subscribe_plan']) && $pharmaApiId) {
                $subData = [
                    'pharmaId' => $pharmaApiId,
                    'doctorId' => $apiId,
                    'amount' => $data['amount'] ?? 1179,
                    'years' => $data['years'] ?? 1,
                    'planId' => $data['planId'] ?? null,
                ];
                $this->pinktreeApiService->subscribePlan($subData);
            }

            // Step 3: Always send WhatsApp onboarding link after doctor creation
            $onboardingLink = env('DOCTOR_ONBOARDING_LINK', 'https://play.google.com/store/apps/details?id=com.pinktreehealth');
            $this->whatsAppService->sendOnboardingLink($data['mobile_no'], $onboardingLink, $data['name']);

            return $localDoctor;

        } catch (\Exception $e) {
            Log::error('[DoctorCreation] Doctor creation process failed.', [
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

