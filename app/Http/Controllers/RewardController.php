<?php

namespace App\Http\Controllers;

use App\Services\RewardEngineService;
use Illuminate\Http\Request;

class RewardController extends Controller
{
     public function generate(Request $request)
    {
        RewardEngineService::run(
            (int)$request->month,
            (int)$request->year
        );

        return response()->json([
            'message' => 'Rewards generated successfully'
        ]);
    }
}
