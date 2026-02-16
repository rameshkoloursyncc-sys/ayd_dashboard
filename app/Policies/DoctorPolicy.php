<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;
use App\Models\MedicalExecutive;
use Illuminate\Auth\Access\Response;

class DoctorPolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->role === 'super_admin') {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['pharma_admin', 'medical_executive']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Doctor $doctor): bool
    {
        if ($user->role === 'pharma_admin') {
            return $user->pharma_company_id === $doctor->pharma_company_id;
        }

        if ($user->role === 'medical_executive') {
            $medicalExecutive = MedicalExecutive::where('user_id', $user->id)->first();
            return $medicalExecutive && $medicalExecutive->id === $doctor->medical_executive_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'pharma_admin', 'medical_executive']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Doctor $doctor): bool
    {
        if ($user->role === 'pharma_admin') {
            return $user->pharma_company_id === $doctor->pharma_company_id;
        }

        if ($user->role === 'medical_executive') {
            $medicalExecutive = MedicalExecutive::where('user_id', $user->id)->first();
            return $medicalExecutive && $medicalExecutive->id === $doctor->medical_executive_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Doctor $doctor): bool
    {
        if ($user->role === 'pharma_admin') {
            return $user->pharma_company_id === $doctor->pharma_company_id;
        }

        if ($user->role === 'medical_executive') {
            $medicalExecutive = MedicalExecutive::where('user_id', $user->id)->first();
            return $medicalExecutive && $medicalExecutive->id === $doctor->medical_executive_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Doctor $doctor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Doctor $doctor): bool
    {
        return false;
    }
}