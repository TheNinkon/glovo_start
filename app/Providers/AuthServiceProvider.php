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

use App\Policies\AccountPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\RiderPolicy;
use App\Policies\Policies\ForecastPolicy;
use App\Policies\Policies\ForecastSlotPolicy;
use App\Policies\Policies\RiderSchedulePolicy;
use App\Policies\Policies\RiderWildcardPolicy;
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
        Forecast::class => ForecastPolicy::class,
        ForecastSlot::class => ForecastSlotPolicy::class,
        RiderSchedule::class => RiderSchedulePolicy::class,
        RiderWildcard::class => RiderWildcardPolicy::class,
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
