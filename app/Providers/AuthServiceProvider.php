<?php

namespace App\Providers;

// Añadimos las importaciones completas a los Modelos y Policies
use App\Models\Account;
use App\Models\Assignment;
use App\Models\Rider;
use App\Models\Forecast;
use App\Models\ForecastSlot;
use App\Models\RiderSchedule;
use App\Models\RiderWildcard;
use App\Models\RiderForecastState;

use App\Policies\AccountPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\RiderPolicy;
use App\Policies\Policies\ForecastPolicy;
use App\Policies\Policies\ForecastSlotPolicy;
use App\Policies\Policies\RiderSchedulePolicy;
use App\Policies\Policies\RiderWildcardPolicy;
use App\Policies\RiderForecastStatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Forecast::class => ForecastPolicy::class,
        ForecastSlot::class => ForecastSlotPolicy::class,
        RiderSchedule::class => RiderSchedulePolicy::class,
        RiderWildcard::class => RiderWildcardPolicy::class,
        RiderForecastState::class => RiderForecastStatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Registrar policies mapeadas
        $this->registerPolicies();

        // Allow all abilities to super-admin as a global override
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('super-admin') ? true : null;
        });
    }
}
