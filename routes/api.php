<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // User routes
    Route::prefix('users')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::post('profile/image', [UserController::class, 'uploadProfileImage']);
        Route::delete('profile/image/{id}', [UserController::class, 'deleteProfileImage']);
        Route::get('search', [UserController::class, 'searchUsers']);
        Route::get('sessions', [UserController::class, 'getSessions']);
        Route::delete('sessions/{id}', [UserController::class, 'deleteSession']);
    });

    // Chat routes
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('{id}', [ChatController::class, 'show']);
        Route::put('{id}', [ChatController::class, 'update']);
        Route::delete('{id}', [ChatController::class, 'destroy']);
        Route::post('{id}/members', [ChatController::class, 'addMember']);
        Route::delete('{id}/members/{userId}', [ChatController::class, 'removeMember']);
        Route::put('{id}/members/{userId}/role', [ChatController::class, 'updateMemberRole']);
        Route::post('{id}/invite-link', [ChatController::class, 'generateInviteLink']);
        Route::post('join-by-link', [ChatController::class, 'joinByLink']);
    });

    // Message routes
    Route::prefix('messages')->group(function () {
        Route::get('chat/{chatId}', [MessageController::class, 'getChatMessages']);
        Route::post('/', [MessageController::class, 'store']);
        Route::get('{id}', [MessageController::class, 'show']);
        Route::put('{id}', [MessageController::class, 'update']);
        Route::delete('{id}', [MessageController::class, 'destroy']);
        Route::post('{id}/read', [MessageController::class, 'markAsRead']);
        Route::post('{id}/forward', [MessageController::class, 'forward']);
        Route::post('typing', [MessageController::class, 'startTyping']);
        Route::delete('typing', [MessageController::class, 'stopTyping']);
    });
});
