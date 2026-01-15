<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function mytask()
    {
        $tasks = Task::where('assigned_to', auth()->id())
            ->with([
                'assignedBy:id,name',
                'assignedTo:id,name',
                'logs' => function ($q) {
                    $q->with('employee:id,name')
                        ->latest();
                }
            ])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'ok',
            'tasks' => $tasks,
        ]);
    }



    public function saveTaskLog(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'message' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('task_logs', 'public');
        }


        $log = TaskLog::create([
            'task_id' => $request->task_id,
            'employee_id' => auth()->id(),
            'note' => $request->message,
            'image' => $path ?? null,
        ]);


        return response()->json([
            'status' => true,
            'data' => $log
        ], 201);
    }
}
