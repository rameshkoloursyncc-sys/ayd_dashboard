<?php

namespace App\Providers;

use App\Models\MedicalExecutive;
use App\Policies\MedicalExecutivePolicy;
use App\Models\Doctor;
use App\Policies\DoctorPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        MedicalExecutive::class => MedicalExecutivePolicy::class,
        Doctor::class => DoctorPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
