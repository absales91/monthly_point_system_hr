<?php

namespace App\Http\Controllers;

use App\Models\EmployeeOfMonth;
use App\Models\EmployeeReward;
use App\Models\RewardWallet;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('dashboard', [
            'employeeOfMonths' => EmployeeOfMonth::with('employee')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get(),
            'myWallet' => RewardWallet::where('employee_id', $user->id)->first(),
            'myRewards' => EmployeeReward::with('rule')
            ->where('employee_id', auth()->user()->id)
            ->latest()
            ->take(5)
            ->get()
        ]);
    }
}
