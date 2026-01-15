<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use App\Models\TaskLog;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminTaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('employee')
            ->latest()
            ->paginate(10);

        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        return view('admin.tasks.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        // ✅ Get employee user
        $employee = User::where('role', 'employee')
            ->where('id', $request->assigned_to)
            ->firstOrFail();

        // ✅ Create task and STORE IT
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_by' => auth()->id(),        // 🔥 FIXED
            'assigned_to' => $employee->id,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);

        // ✅ Send notification AFTER task is created
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

    public function show(Task $task)
    {
        $task->load(['assignedBy:id,name', 'assignedTo:id,name', 'logs.employee:id,name,role']);

        // ✅ append image_url for each log
        $task->logs->each(function ($log) {
            $log->image = $log->image
                ? asset($log->image)
                : null;
        });

        return view('admin.tasks.show', compact('task'));
    }
    public function storeLog(Request $request, Task $task)
    {
        // 🔐 Authorization (extra safety)
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // ✅ Validation
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // 📂 Upload image (if exists)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('task-logs', 'public');
        }

        // 💾 Save task log
        TaskLog::create([
            'task_id' => $task->id,
            'employee_id' => auth()->id(),
            'note' => $validated['message'],
            'image'   => $imagePath,
        ]);

        // 🔄 Optional: auto-update task status
        if ($task->status === 'pending') {
            $task->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Task update added successfully.');
    }

    public function destroy(Task $task)
    {
        // 🔐 Allow only admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // 🗑 Delete related task logs images
        foreach ($task->logs as $log) {

            if ($log->image && File::exists(public_path($log->image))) {
                File::delete(public_path($log->image));
            }
            $log->delete();
        }

        // 🗑 Delete task (logs will auto-delete if cascade is set)
        $task->delete();

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
