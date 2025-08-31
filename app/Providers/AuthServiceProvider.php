<?php

namespace App\Providers;

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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Rider::class => RiderPolicy::class,
        Account::class => AccountPolicy::class, // <-- Asegúrate de que esta línea exista
        Assignment::class => AssignmentPolicy::class, // <-- Y esta también
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
