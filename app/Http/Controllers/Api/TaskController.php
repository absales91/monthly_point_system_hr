<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;

class TaskController extends Controller
{
    public function mytask()
    {
        $tasks = Task::where('assigned_to', auth()->id())
            ->with('assignedBy', 'assignedTo')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'ok',
            'tasks' => $tasks,
        ]);
    }
}