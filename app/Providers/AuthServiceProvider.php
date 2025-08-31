<?php

namespace App\Providers;

// Importaciones corregidas para apuntar a la ubicación real de tus archivos
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
        // Usamos la sintaxis ::class para evitar errores de tipeo y de namespace
        Rider::class => RiderPolicy::class,
        Account::class => AccountPolicy::class,
        Assignment::class => AssignmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Esta línea es importante para que se registren las policies
        $this->registerPolicies();
    }
}
