<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RewardsController extends Controller
{
      public function index(Request $request)
    {
        $user = $request->user();

        // 🪙 WALLET (TOTALS)
        $wallet = DB::table('reward_wallets')
            ->where('employee_id', $user->id)
            ->first();

        $availablePoints = $wallet->available_points ?? 0;
        $lifetimePoints  = $wallet->lifetime_points ?? 0;

        // 📜 REWARD HISTORY (employee_rewards)
        $history = DB::table('employee_rewards as er')
            ->leftJoin('reward_rules as rr', 'rr.id', '=', 'er.reward_rule_id')
            ->where('er.employee_id', $user->id)
            ->orderBy('er.year', 'desc')
            ->orderBy('er.month', 'desc')
            ->orderBy('er.created_at', 'desc')
            ->get()
            ->map(function ($row) {

                // earned vs redeemed logic
                $type = $row->points_used > 0 ? 'redeemed' : 'earned';

                // display points
                $points = $row->points_used > 0
                    ? $row->points_used
                    : $row->reward_value;

                return [
                    'title' => $row->title ?? 'Reward',
                    'points' => (int) $points,
                    'type' => $type,
                    'status' => $row->status, // pending / approved / used
                    'month' => $row->month,
                    'year' => $row->year,
                    'date' => Carbon::create($row->year, $row->month, 1)
                        ->format('M Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'available_points' => $availablePoints,
                'lifetime_points' => $lifetimePoints,
                'history' => $history,
            ]
        ]);
    }
}
