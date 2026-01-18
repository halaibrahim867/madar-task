<?php

use App\Events\TestPusherEvent;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Chat\ChatController;
use App\Http\Controllers\Api\V1\Pdf\PdfController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::post('auth/login', [AuthController::class, 'login']);

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

    Route::get('/qdrant-test', function() {
        try {
            $points = new \Mcpuishor\QdrantLaravel\PointsCollection([
                new \Mcpuishor\QdrantLaravel\DTOs\Point(
                    id: 'test-point',
                    vector: array_fill(0, (int) config('qdrant-laravel.connections.main.vector_size'), 0.01),
                    payload: ['test'=>'hello']
                )
            ], config('qdrant-laravel.connections.main.collection')); // pass collection explicitly

            return "Upsert OK";
        } catch (\Throwable $e) {
            return "ERROR: " . get_class($e) . " - " . $e->getMessage();
        }
    });

});
