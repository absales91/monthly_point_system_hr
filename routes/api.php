<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController as Dashboard;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\RewardsController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [Dashboard::class, 'index']);
   Route::post('/attendance/punch', [AttendanceController::class, 'punch']);
    Route::get('/attendance/summary', [AttendanceController::class, 'attendanceSummary']);
    Route::get('/attendance/last-punch', [AttendanceController::class, 'lastPunch']);
      Route::get('/attendance/punches', [AttendanceController::class, 'punchesByDate']);
    //   Daily Reports
    Route::post('/daily-report', [DailyReportController::class, 'submitDailyReport']);
    Route::get('/daily-report/today', [DailyReportController::class, 'myTodayReport']);
    Route::get('/daily-report', [DailyReportController::class, 'myReports']);
    // Rewards
    Route::get('/rewards', [RewardsController::class, 'index']);
    Route::post('/save-fcm-token', function (Request $request) {
    $request->validate([
        'fcm_token' => 'nullable|string',
    ]);

    $request->user()->update([
        'fcm_token' => $request->fcm_token,
    ]);

    return response()->json([
        'status' => 'ok'
    ]);

});
 Route::get('/mytask',[TaskController::class,'mytask']);
 // save task log
    Route::post('/task-logs-save',[TaskController::class,'saveTaskLog']);

    Route::prefix('leads')->group(function () {

    // Get leads list
    Route::get('/', [LeadController::class, 'index']);

    // Get single lead
    Route::get('{id}', [LeadController::class, 'show']);

    // Update lead status
    Route::post('{id}/status', [LeadController::class, 'updateStatus']);

});
});
