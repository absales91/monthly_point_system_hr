<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // employee

        // 🪙 TOTAL POINTS (wallet)
        $totalPoints = DB::table('reward_wallets')
            ->where('employee_id', $user->id)
            ->sum(DB::raw("
                CASE 
                    WHEN type = 'earned' THEN points 
                    WHEN type = 'redeemed' THEN -points 
                    ELSE 0 
                END
            "));

        // 📜 HISTORY
        $history = DB::table('reward_wallets')
            ->where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'title' => $row->title,
                    'points' => (int) $row->points,
                    'type' => $row->type, // earned | redeemed
                    'date' => Carbon::parse($row->created_at)->format('d M Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_points' => max(0, $totalPoints),
                'history' => $history,
            ]
        ]);
    }
}
