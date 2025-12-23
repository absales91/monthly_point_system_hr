<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PointRule;
use App\Models\MonthlyPoint;
use Illuminate\Http\Request;

class MonthlyReportController extends Controller
{
    public function create()
    {
        abort_unless(auth()->user()->canManage(), 403);

        return view('monthly-reports.create', [
            'employees' => User::where('role', 'employee')->get(),
            'rules'     => PointRule::orderBy('id')->get(),
        ]);
    }

 public function store(Request $request)
{
    abort_unless(auth()->user()->canManage(), 403);

    // ✅ Validate base input
    $request->validate([
        'employee_id' => 'required|exists:users,id',
        'month'       => 'required|date_format:Y-m',
    ]);

    // ❌ Prevent duplicate (employee + month)
    $exists = MonthlyPoint::where('employee_id', $request->employee_id)
        ->where('month', $request->month)
        ->exists();

    if ($exists) {
        return back()
            ->withErrors([
                'month' => 'Monthly report already exists for this employee.'
            ])
            ->withInput();
    }

    $rules = PointRule::all();

    $data = [
        'employee_id' => $request->employee_id,
        'month'       => $request->month,
    ];

    $total = 0;

    foreach ($rules as $rule) {

        // 🔐 Role-based protection
        if ($rule->manager_only && !auth()->user()->canManage()) {
            $value = 0;
        } else {
            $value = (int) $request->input($rule->category, 0);
            $value = min($value, $rule->max_points);
        }

        $data[$rule->category] = $value;
        $total += $value;
    }

    $total = min($total, 100);

    $rating = match (true) {
        $total >= 90 => 'Excellent',
        $total >= 75 => 'Good',
        $total >= 60 => 'Average',
        default      => 'Poor',
    };

    MonthlyPoint::create([
        ...$data,
        'total'  => $total,
        'rating' => $rating,
    ]);

    return redirect()
        ->route('dashboard')
        ->with('success', 'Monthly report added successfully');
}

  public function index(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $query = MonthlyPoint::with('employee');

        // 🔍 Filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        return view('monthly-reports.index', [
            'reports'   => $query->orderBy('month', 'desc')->get(),
            'employees' => User::where('role', 'employee')->get(),
        ]);
    }

}
