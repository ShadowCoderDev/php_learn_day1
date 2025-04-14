<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\UserAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// مسیرهای کاربران عادی
Route::prefix('user')->group(function () {
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);

    // مسیرهای محافظت شده کاربران
    Route::middleware(['auth:api', 'scope:user'])->group(function () {
        Route::get('/profile', [UserAuthController::class, 'user']);
        Route::post('/logout', [UserAuthController::class, 'logout']);
    });
});

// مسیرهای مدیران
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    // مسیرهای محافظت شده مدیران
    Route::middleware(['auth:api', 'scope:admin'])->group(function () {
        Route::get('/profile', [AdminAuthController::class, 'admin']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/create', [AdminAuthController::class, 'createAdmin']);
    });
});
