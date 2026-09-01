<?php

use App\Http\Controllers\Api\SgsstController;
use Illuminate\Support\Facades\Route;

Route::prefix('sgsst')->group(function () {
    Route::post('/register', [SgsstController::class, 'register']);
    Route::post('/login',    [SgsstController::class, 'login']);
});

Route::prefix('webhooks')->group(function () {
    Route::post('/alegra/{tenantId}/invoices', [\App\Http\Controllers\Api\AlegraWebhookController::class, 'handle']);
});
