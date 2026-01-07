<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function submitDailyReport(Request $request)
    {
        $request->validate([
            'work_summary' => 'required|string|min:10',
        ]);

        $employeeId = $request->user()->id;
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $report = DailyReport::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $today,
            ],
            [
                'work_summary' => $request->work_summary,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily report saved',
            'data' => $report,
        ]);
    }
    public function myTodayReport(Request $request)
    {
        $employeeId = $request->user()->id;
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $report = DailyReport::where('employee_id', $employeeId)
            ->where('date', $today)
            ->first();

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }
    public function myReports(Request $request)
    {
        $employeeId = $request->user()->id;
        $month = $request->get('month', now()->format('Y-m'));

        $reports = DailyReport::where('employee_id', $employeeId)
            ->whereMonth('date', substr($month, 5, 2))
            ->whereYear('date', substr($month, 0, 4))
            ->orderByDesc('date')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports,
        ]);
    }
}
