<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController as Dashboard;
use App\Http\Controllers\Api\RewardController;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [Dashboard::class, 'index']);
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    // attendance Summary
    Route::get('/attendance/summary', [AttendanceController::class, 'attendanceSummary']);
    // Rewards
    Route::get('/rewards', [RewardController::class, 'index']);
});