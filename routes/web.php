<?php

use App\Http\Controllers\web\AuthController;
use App\Http\Controllers\web\DashboardController;
use Illuminate\Support\Facades\Route;



Route::get('/', [AuthController::class, 'index'])->name('login');
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'index')->name('web.login');
    Route::post('login', 'login')->name('web.loginPost');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::controller(DashboardController::class)->name('dashboard.')->group(function () {
        Route::get('users', 'users')->name('users');
        Route::get('/', 'index')->name('index');
    });

    Route::get('logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('test',[\App\Http\Controllers\web\GroupScheduleController::class,'test'])->name('test');
});
