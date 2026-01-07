<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController as Dashboard;
use App\Http\Controllers\Api\RewardsController;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [Dashboard::class, 'index']);
   Route::post('/attendance/punch', [AttendanceController::class, 'punch']);
    Route::get('/attendance/summary', [AttendanceController::class, 'attendanceSummary']);
    Route::get('/attendance/last-punch', [AttendanceController::class, 'lastPunch']);
    // Rewards
    Route::get('/rewards', [RewardsController::class, 'index']);
});