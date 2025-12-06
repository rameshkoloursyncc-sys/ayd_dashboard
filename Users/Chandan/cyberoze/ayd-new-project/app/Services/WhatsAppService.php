<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppService
{
    public function sendOnboardingLink(string $phoneNumber, string $onboardingLink, string $doctorName = null): bool
    {
        $apiUrl = 'https://whatsparrot.com/api/wpbox/sendtemplatemessage';
        $token = env('WHATSAPP_API_TOKEN');

        // Sanitize phone number: remove + and non-digits
        $sanitizedPhone = preg_replace('/\D/', '', $phoneNumber);

        // Use doctorName and onboardingLink as template parameters
        $payload = [
            'token' => $token,
            'phone' => $sanitizedPhone,
            'template_name' => 'invite_doctor_2',
            'template_language' => 'en',
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $doctorName ?? 'Doctor'
                        ],
                        [
                            'type' => 'text',
                            'text' => $onboardingLink ?? 'https://play.google.com/store/apps/details?id=com.pinktreehealth&pcampaignid=web_share'
                        ]
                    ]
                ]
            ]
        ];

        // Log the payload, phone, doctor name, and onboarding link for traceability
        Log::info("WhatsApp API payload", [
            'phone' => $sanitizedPhone,
            'doctor_name' => $doctorName,
            'onboarding_link' => $onboardingLink,
            'payload' => $payload
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl, $payload);
            Log::info("WhatsParrot API response", [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp onboarding template message: " . $e->getMessage());
            return false;
        }
    }

    public function notifyOnboardingSuccess(string $phoneNumber, string $doctorName): bool
    {
        // Placeholder for WhatsApp API integration
        // In a real application, you would use a WhatsApp Business API client
        // to send the success notification.
        Log::info("Notifying $doctorName of successful onboarding to $phoneNumber.");

        return true; // Assume success for now
    }
}
