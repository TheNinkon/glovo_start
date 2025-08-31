<?php

namespace App\Providers;

// Añadimos las importaciones completas a los Modelos y Policies
use App\Models\Account;
use App\Models\Assignment;
use App\Models\Rider;
use App\Policies\AccountPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\RiderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Usamos la sintaxis ::class para asegurar la resolución correcta
        Rider::class => RiderPolicy::class,
        Account::class => AccountPolicy::class,
        Assignment::class => AssignmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
