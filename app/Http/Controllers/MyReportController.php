<?php

namespace App\Http\Controllers;

use App\Models\MonthlyPoint;
use Illuminate\Http\Request;

class MyReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $reports = MonthlyPoint::where('employee_id', $user->id)
            ->orderBy('month', 'desc')
            ->get();

        return view('my-report.index', compact('reports'));
    }
}
