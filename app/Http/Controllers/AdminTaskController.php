<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('employee')
            ->latest()
            ->paginate(10);

        return view('admin.tasks.index', compact('tasks'));
    }

   public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'assigned_to' => 'required|exists:users,id',
        'priority' => 'required|in:low,medium,high',
        'due_date' => 'nullable|date',
    ]);

    // ✅ Get employee
    $employee = User::where('role', 'employee')
        ->where('id', $request->assigned_to)
        ->firstOrFail();

    // ✅ Create task (SAVE TO VARIABLE)
    $task = Task::create([
        'title' => $request->title,
        'description' => $request->description,
        'assigned_by' => auth()->id(),   // 🔥 FIXED
        'assigned_to' => $employee->id,
        'priority' => $request->priority,
        'due_date' => $request->due_date,
        'status' => 'pending',
    ]);

    // ✅ Send notification
    NotificationService::send(
        $employee,
        'New Task Assigned',
        $task->title,
        'task_assigned',
        $task->id
    );

    return redirect()
        ->route('admin.tasks.index')
        ->with('success', 'Task created successfully');
}

}