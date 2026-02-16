# Session Changes Record
Date: December 9, 2025

## 1. Feature: Quota Management
**Goal:** Increment `usedActivationQuota` on Pharma Company when a doctor is created or attached.

*   **`app/Services/PinktreeApiService.php`**
    *   Added `incrementUsedActivationQuota($pharmaApiId, $increment)`: Fetches pharma, calculates new quota, and updates it.
*   **`app/Services/DoctorCreationService.php`**
    *   Updated `create()`: Calls `incrementUsedActivationQuota` after successful doctor creation.
*   **`app/Http/Controllers/SuperAdmin/DoctorController.php`**
    *   Updated `attachPharma()`: Calls `incrementUsedActivationQuota` when attaching a doctor.

## 2. Feature: Subscription Plan
**Goal:** Assign a subscription plan (Amount, Duration) to a Pharma Company for a Doctor.

*   **`app/Services/PinktreeApiService.php`**
    *   Added `subscribePlan(array $data)`: POSTs to `/api/plan/subscribe`.
*   **`app/Services/DoctorCreationService.php`**
    *   Updated `create()`: Checks for `subscribe_plan` flag and calls `subscribePlan`.
*   **`app/Http/Controllers/SuperAdmin/DoctorController.php`**
    *   Updated `update()`: Checks for `subscribe_plan` flag and calls `subscribePlan` (moved from `attachPharma`).
*   **`app/Http/Requests/StoreDoctorRequest.php`**
    *   Added validation rules: `subscribe_plan`, `amount`, `years`, `planId`.

## 3. Feature: ABHA Removal
**Goal:** Remove `abhaa_no` field from all doctor-related forms and API calls.

*   **`app/Services/PinktreeApiService.php`**: Removed `abhaa_no` from `createDoctor` payload.
*   **`app/Services/DoctorCreationService.php`**: Removed `abhaa_no` from payload extraction.
*   **`app/Http/Requests/StoreDoctorRequest.php`**: Removed `abhaa_no` validation.
*   **`resources/views/doctors/create.blade.php`**: Removed ABHA input field.
*   **`resources/views/doctors/edit.blade.php`**: Removed ABHA input field.
*   **`resources/views/doctors/show.blade.php`**: Verified ABHA field is removed.

## 4. UI/UX Updates
*   **`resources/views/doctors/create.blade.php`**
    *   Added "Assign Subscription to Pharma" checkbox.
    *   Added conditional fields for Amount (default 1179) and Duration (1 year).
*   **`resources/views/doctors/edit.blade.php`**
    *   Added "Assign Subscription to Pharma" section (visible only if Pharma is attached).
*   **`resources/views/superadmin/doctors/index.blade.php`**
    *   Added "Attach Pharma" modal for doctors without a local mapping.
    *   Fixed `openAttachPharmaModal` JavaScript error.
    *   (Note: Subscription fields were initially added here but then removed per request).

## 5. Routing
*   **`routes/web.php`**
    *   Added route: `POST doctors/{api_id}/attach-pharma` -> `SuperAdminDoctorController@attachPharma`.
