<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\CreditPurchase;
use App\Models\GiftType;
use App\Models\Pet;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\CreditPurchasePolicy;
use App\Policies\GiftTypePolicy;
use App\Policies\PetPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Service provider for authentication and authorization.
 *
 * @group Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Pet::class => PetPolicy::class,
        User::class => UserPolicy::class,
        GiftType::class => GiftTypePolicy::class,
        CreditPurchase::class => CreditPurchasePolicy::class,
        Appointment::class => AppointmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
