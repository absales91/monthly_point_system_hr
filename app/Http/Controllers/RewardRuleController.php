<?php

namespace App\Http\Controllers;

use App\Models\RewardRule;
use Illuminate\Http\Request;

class RewardRuleController extends Controller
{
    public function index()
    {
        return view('reward_rules.index', [
            'rules' => RewardRule::latest()->get()
        ]);
    }

    public function create()
    {
        return view('reward_rules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'reward_type'     => 'required',
            'reward_name'     => 'required',
            'point_threshold' => 'required|integer|min:1',
            'reward_value'    => 'required|integer|min:1',
            'max_per_month'   => 'required|integer|min:1',
        ]);

        RewardRule::create([
            'reward_type'     => $request->reward_type,
            'reward_name'     => $request->reward_name,
            'point_threshold' => $request->point_threshold,
            'reward_value'    => $request->reward_value,
            'max_per_month'   => $request->max_per_month,
            'carry_forward'   => $request->has('carry_forward'),
            'is_active'       => true,
        ]);

        return redirect()
            ->route('reward-rules.index')
            ->with('success', 'Reward rule created successfully');
    }
}
