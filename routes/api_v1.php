<?php

use App\Events\TestPusherEvent;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Chat\ChatController;
use App\Http\Controllers\Api\V1\Pdf\PdfController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('pdf/upload',[PdfController::class, 'upload']);
        Route::post('chat/send', [ChatController::class, 'send']);

    });

    Route::get('/test-pusher', function () {
        try {
            broadcast(new \App\Events\TestPusherEvent('This is a test message'));
            return 'Broadcast fired';
        } catch (\Throwable $e) {
            \Log::error('Pusher error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 'Error';
        }
    });
});
