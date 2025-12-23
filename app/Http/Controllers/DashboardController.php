<?php

namespace App\Http\Controllers;

use App\Models\EmployeeOfMonth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'employeeOfMonths' => EmployeeOfMonth::with('employee')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get(),
        ]);
    }
}
