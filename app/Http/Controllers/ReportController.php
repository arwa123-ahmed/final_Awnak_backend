<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ServiceMatch;
use App\Models\Report;

class ReportController extends Controller
{
    public function store(Request $request , $servicematch_id)
{
    $service_match = ServiceMatch::findOrfail($servicematch_id); 
    $user = $request->user();
   // اول شي نتأكد من الي عم يعمل شكوى
   if ( $service_match->customer_id !== $user->id && $service_match->volunteer_id !== $user->id ) {
        return response()->json([
            'message' => 'You are not part of this service'
        ], 403);
    }

     //  لازم تكون خلصت المهمه بالاول 
    if (!in_array($service_match->status, ['completed', 'Ratinged'])) {
        return response()->json([
            'message' => 'Service not completed yet'
        ], 403);
    }
    if ($user->id == $service_match->customer_id) { // اذا يلي عم يبلغ هة الكوستمر
    $reportedId = $service_match -> volunteer_id; // خلي الفولانتير هو المبلغ عنه 

    } else {
    $reportedId = $service_match -> customer_id;
    }
    Report::create([
        'servicematch_id' => $service_match->id,
        'reporter_id' => $user->id,
        'reported_id' => $reportedId ,
        'reason' => $request->reason,
        'status' => 'pending',
    ]);
    return response()->json(['message' => 'Report submitted'], 201);
}

}