<?php

use App\Http\Controllers\AdminTaskController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeOfMonthController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\MyReportController;
use App\Http\Controllers\PointRuleController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\RewardRuleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth','role:admin,manager'])->group(function(){

    Route::get('/attendance',[AttendanceController::class,'index'])
        ->name('attendance.index');

    Route::post('/attendance/store',[AttendanceController::class,'store'])
        ->name('attendance.store');
});





Route::middleware(['auth','role:admin'])->group(function () {
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

    // 🔁 Reward Rules (ADMIN)
    Route::get('/reward-rules', [RewardRuleController::class, 'index'])
        ->name('reward-rules.index');

    Route::get('/reward-rules/create', [RewardRuleController::class, 'create'])
        ->name('reward-rules.create');
    Route::post('/reward-rule/generate',[RewardController::class,'generate'])
        ->name('reward.generate');

    Route::post('/reward-rules', [RewardRuleController::class, 'store'])
        ->name('reward-rules.store');

    Route::get('/tasks', [AdminTaskController::class, 'index'])
        ->name('admin.tasks.index');

    Route::get('/tasks/create', [AdminTaskController::class, 'create'])
        ->name('admin.tasks.create');

    Route::get('/tasks/{task}', [AdminTaskController::class, 'show'])
        ->name('admin.tasks.show');

    Route::post('/tasks', [AdminTaskController::class, 'store'])
        ->name('admin.tasks.store');
    Route::post('/admin/tasks/{task}/logs', [AdminTaskController::class, 'storeLog'])
    ->name('admin.tasks.logs.store');
    Route::delete('/tasks/{task}', [AdminTaskController::class, 'destroy'])
        ->name('admin.tasks.destroy');

});

Route::middleware(['auth','role:admin,manager'])->group(function () {
    //
    Route::get('/monthly-reports/create', [MonthlyReportController::class, 'create'])
        ->name('monthly-reports.create');

    Route::post('/monthly-reports', [MonthlyReportController::class, 'store'])
        ->name('monthly-reports.store');
// ================= EMPLOYEE OF THE MONTH =================
    Route::get('/employee-of-month',
        [EmployeeOfMonthController::class,'index']
    )->name('employee-of-month.index');

     Route::post('/employee-of-month/announce',
        [EmployeeOfMonthController::class,'announce']
    )->name('employee-of-month.announce');
// ==========================================================
});

Route::middleware(['auth','role:employee,manager'])->group(function(){
    Route::get('/my-report', [MyReportController::class, 'index'])
    ->name('my-report');

      Route::get('/my-attendance',[AttendanceController::class,'myAttendance'])
        ->name('attendance.my');
    Route::get('/attendance/{date}', 
    [AttendanceController::class, 'show']
)->name('employee.attendance.show');

    Route::post('/attendance/check-in',[AttendanceController::class,'checkIn'])
        ->name('attendance.checkin');

    Route::post('/attendance/check-out',[AttendanceController::class,'checkOut'])
        ->name('attendance.checkout');

     Route::get('/employee/salary-slip/{month}/{year}',
        [EmployeeController::class,'salarySlipDownload']
    )->name('salary-slip.download');
});


Route::middleware(['auth','role:employee'])->group(function(){

   

   
});

    

require __DIR__.'/auth.php';
