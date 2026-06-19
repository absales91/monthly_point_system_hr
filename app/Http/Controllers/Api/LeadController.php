<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query();

        // Optional filters
        if ($request->filled('status')) {
            $query->where('lead_status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('query_time', [
                $request->from_date,
                $request->to_date
            ]);
        }

        // Latest leads first
        $leads = $query->orderBy('query_time', 'desc')
                       ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'total' => $leads->total(),
            'current_page' => $leads->currentPage(),
            'last_page' => $leads->lastPage(),
            'leads' => $leads->items(),
        ]);
    }

    /**
     * Single lead detail
     */
    public function show($id)
    {
        $lead = Lead::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'lead' => $lead
        ]);
    }

    /**
     * Update lead status (Follow-up / Converted / Lost)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'lead_status' => 'required|string'
        ]);

        $lead = Lead::findOrFail($id);
        $lead->update([
            'lead_status' => $request->lead_status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead status updated',
            'lead' => $lead
        ]);
    }
}
