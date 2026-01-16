<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// routes/api.php
Route::middleware('auth:sanctum')->post('v1/broadcasting/auth', function () {
    return Broadcast::auth(request());
});

require __DIR__ . '/api_v1.php';
