<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Pdf\PdfController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('pdf/upload',[PdfController::class, 'upload']);
    });
});
