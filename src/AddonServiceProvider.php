<?php

namespace Pterodactyl\Addons\UserApiKeys;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutes();
    }

    protected function loadRoutes()
    {
        Route::middleware(['api', 'application-api', 'throttle:api.application'])
            ->prefix('/api/application/addons/user-api-keys')
            ->group(base_path('app/Addons/UserApiKeys/routes/api.php'));
    }
}
