<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeOfMonthController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\MyReportController;
use App\Http\Controllers\PointRuleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');



Route::middleware(['auth'])->group(function () {
   Route::get('/employees', [EmployeeController::class, 'index'])
        ->name('employees.index');

    Route::get('/employees/create', [EmployeeController::class, 'create'])
        ->name('employees.create');

    Route::post('/employees', [EmployeeController::class, 'store'])
        ->name('employees.store');

          Route::get('/point-rules', [PointRuleController::class, 'index'])
        ->name('point-rules.index');

    Route::get('/point-rules/create', [PointRuleController::class, 'create'])
        ->name('point-rules.create');

    Route::post('/point-rules', [PointRuleController::class, 'store'])
        ->name('point-rules.store');

    Route::get('/monthly-reports-all', [MonthlyReportController::class, 'index'])
        ->name('monthly-reports-all.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/monthly-reports/create', [MonthlyReportController::class, 'create'])
        ->name('monthly-reports.create');

    Route::post('/monthly-reports', [MonthlyReportController::class, 'store'])
        ->name('monthly-reports.store');
});

Route::middleware(['auth'])->get('/my-report', [MyReportController::class, 'index'])
    ->name('my-report');

    Route::middleware(['auth'])->group(function () {

    Route::get('/employee-of-month',
        [EmployeeOfMonthController::class,'index']
    )->name('employee-of-month.index');

    Route::post('/employee-of-month/announce',
        [EmployeeOfMonthController::class,'announce']
    )->name('employee-of-month.announce');

});


require __DIR__.'/auth.php';
