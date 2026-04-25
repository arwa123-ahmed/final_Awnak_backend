<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ServiceMatch;
use App\Models\Report;

class ReportController extends Controller
{
    // ✅ أضيفي الـ index function
//     public function index()
// {
//     $reports = Report::with(['reporter', 'reportedUser'])
//         ->orderBy('created_at', 'desc')
//         ->get();

//     return response()->json($reports);
// }
public function index()
{
    $reports = Report::with(['reporter', 'reportedUser'])
        ->orderBy('created_at', 'desc')
        ->get();

    // ✅ أضيفي الـ chat messages لكل report
    $reports = $reports->map(function ($report) {
        // جيبي الـ match بين الاتنين
        $match = \App\Models\ServiceMatch::where(function ($q) use ($report) {
            $q->where('customer_id', $report->reporter_id)
              ->where('volunteer_id', $report->reported_id);
        })->orWhere(function ($q) use ($report) {
            $q->where('customer_id', $report->reported_id)
              ->where('volunteer_id', $report->reporter_id);
        })->latest()->first();

        // جيبي الـ messages
        $report->full_chat_history = $match
            ? \App\Models\Message::with('sender:id,name')
                ->where('service_match_id', $match->id)
                ->orderBy('created_at', 'asc')
                ->get()
            : [];

        return $report;
    });

    return response()->json($reports);
}
    public function store(Request $request, $servicematch_id)
    {
        $service_match = ServiceMatch::findOrFail($servicematch_id);
        $user = $request->user();

        if ($service_match->customer_id !== $user->id && $service_match->volunteer_id !== $user->id) {
            return response()->json(['message' => 'You are not part of this service'], 403);
        }

        if (!in_array($service_match->status, ['completed', 'Ratinged', 'rated'])) {
            return response()->json(['message' => 'Service not completed yet'], 403);
        }

        if ($user->id == $service_match->customer_id) {
            $reportedId = $service_match->volunteer_id;
        } else {
            $reportedId = $service_match->customer_id;
        }

        Report::create([
            'servicematch_id' => $service_match->id,
            'reporter_id'     => $user->id,
            'reported_id'     => $reportedId,
            'reason'          => $request->reason,
            'status'          => 'pending',
        ]);

        return response()->json(['message' => 'Report submitted'], 201);
    }
}