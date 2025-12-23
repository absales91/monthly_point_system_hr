<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('employees.index', [
            'employees' => User::whereIn('role', ['employee'])->get()
        ]);
    }

    public function create()
    {

        return view('employees.create');
    }

    public function store(Request $request)
    {
        abort_unless(isAdmin(), 403);

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:admin,manager,employee',
            'password' => 'required|string|min:6|confirmed',
        ]);


        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
            // 'status'   => 1,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully');
    }
}
