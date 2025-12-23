<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmployeeOfMonthService;
use App\Models\EmployeeOfMonth;

class EmployeeOfMonthController extends Controller
{
    public function index()
    {
        return view('employee-of-month.index', [
            'records' => EmployeeOfMonth::with('employee')
                ->orderBy('month','desc')
                ->get()
        ]);
    }

    public function announce(Request $request)
    {
        abort_unless(auth()->user()->isAdminOrManager(), 403);

        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        try {
            EmployeeOfMonthService::generate($request->month);
        } catch (\Exception $e) {
            dd($e);
            return back()->withErrors(['month' => $e->getMessage()]);
        }

        return redirect()->route('employee-of-month.index')
            ->with('success', 'Employee of the Month announced 🎉');
    }
}
