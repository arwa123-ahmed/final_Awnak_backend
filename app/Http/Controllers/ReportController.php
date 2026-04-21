<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ServiceMatch;
use App\Models\Report;

class ReportController extends Controller
{
    // ✅ أضيفي الـ index function
    public function index()
    {
        $reports = Report::with(['reporter', 'reported'])
            ->orderBy('created_at', 'desc')
            ->get();

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