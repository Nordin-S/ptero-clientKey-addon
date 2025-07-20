<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Addons\UserApiKeys\Http\Controllers\ApiKeyController;

Route::group(['prefix' => '/users/{user}'], function () {
    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{identifier}', [ApiKeyController::class, 'delete']);
});
