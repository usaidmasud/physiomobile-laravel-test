<?php

use App\Http\Middleware\AccessKeyMiddleware;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::middleware(AccessKeyMiddleware::class)->group(function () {
    Route::apiResource("/patient", PatientController::class)->parameters([
        "patient" => "id",
    ]);

});
