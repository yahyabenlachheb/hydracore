<?php

use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\TrialRequestController;
use Illuminate\Support\Facades\Route;

// Called by the brand websites, with the brand's API key. See "Connecting the website" in the README.
Route::prefix('v1')->middleware(['brand.api', 'throttle:60,1'])->group(function () {
    Route::post('/trials', TrialRequestController::class)->name('api.trials.store');
    Route::post('/contacts', ContactMessageController::class)->name('api.contacts.store');
});
