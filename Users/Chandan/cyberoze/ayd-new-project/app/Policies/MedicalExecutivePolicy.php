<?php

namespace App\Policies;

use App\Models\MedicalExecutive;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicalExecutivePolicy
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
        return $user->role === 'pharma_admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MedicalExecutive $medicalExecutive): bool
    {
        return $user->role === 'pharma_admin' && $user->pharma_company_id === $medicalExecutive->pharma_company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'pharma_admin' || $user->role === 'super_admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MedicalExecutive $medicalExecutive): bool
    {
        return $user->role === 'pharma_admin' && $user->pharma_company_id === $medicalExecutive->pharma_company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MedicalExecutive $medicalExecutive): bool
    {
        return $user->role === 'pharma_admin' && $user->pharma_company_id === $medicalExecutive->pharma_company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MedicalExecutive $medicalExecutive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MedicalExecutive $medicalExecutive): bool
    {
        return false;
    }
}