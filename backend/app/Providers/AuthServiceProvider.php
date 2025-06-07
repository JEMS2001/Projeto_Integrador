<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Configure Passport scopes
        Passport::tokensCan([
            'empresa' => 'Company user access',
            'membro' => 'Member user access',
            'company-management' => 'Manage company settings and data',
            'employee-management' => 'Manage company employees',
            'task-management' => 'Create and manage tasks',
            'profile-management' => 'Manage user profile',
        ]);

        // Set default scopes
        Passport::setDefaultScope([
            'empresa',
            'membro',
        ]);

        // Configure token expiration
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addDays(15));
    }
}
