<?php

namespace App\Http\Controllers;

use App\Models\PointRule;
use Illuminate\Http\Request;

class PointRuleController extends Controller
{
    /* 📋 List Rules */
    public function index()
    {
        abort_unless(isAdmin(), 403);

        return view('points.index', [
            'rules' => PointRule::orderBy('id')->get()
        ]);
    }

    /* ➕ Create Form */
    public function create()
    {
        abort_unless(isAdmin(), 403);
        return view('points.create');
    }

    /* 💾 Store */
    public function store(Request $request)
    {
        abort_unless(isAdmin(), 403);

        $request->validate([
            'category'     => 'required|string|max:50|unique:point_rules,category',
            'label'        => 'required|string|max:100',
            'max_points'   => 'required|integer|min:1|max:100',
            'manager_only'=> 'nullable|boolean',
        ]);

        PointRule::create([
            'category'      => $request->category,
            'label'         => $request->label,
            'max_points'    => $request->max_points,
            'manager_only'  => $request->boolean('manager_only'),
        ]);

        return redirect()->route('point-rules.index')
            ->with('success', 'Point rule created');
    }
}
