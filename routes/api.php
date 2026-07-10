<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Register route otorisasi channel private untuk Laravel Echo / Reverb
    Broadcast::routes();

    Route::put('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    Route::get('/users', [AuthController::class, 'users']);
    Route::put('/users/{user}', [AuthController::class, 'updateUser']);

    // Hanya Admin yang bisa menghapus user
    Route::middleware('role:admin')->group(function () {
        Route::delete('/users/{user}', [AuthController::class, 'deleteUser']);
    });

    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
});