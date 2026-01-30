<?php

namespace App\Http\Controllers;
use App\Models\Service;
use App\Models\ServiceMatch;
use Illuminate\Http\Request;

class ProgrammingController extends Controller
{
    
//قرار المتطوع اذا يقبل او يرفض الطلب
public function updateStatusByVolunteer(Request $request, $id)
{
    $user = $request->user();
    if ($user->role !== 'volunteer') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $match = ServiceMatch::where('id', $id)
        ->where('volunteer_id', $user->id)
        ->where('status', 'pending')
        ->first();
    if (!$match) {
        return response()->json(['message' => 'Request not found or already decided'], 404);
    }
    $request->validate([
        'status' => 'required|in:accepted,rejected',
    ]);
    $match->status = $request->status;
    $match->save();
    $service = Service::find($match->service_id);
    if ($match->status === 'accepted') {
        $service->status = 'inprogress';
    } elseif ($match->status === 'rejected') {

        $service->status = 'pending';
    }
    $service->save();
    return response()->json([
        'message' => 'Request has been ' . $request->status,
        'match'   => $match
    ]);
}

//قرار الكاستمر يقبل او يرفض الطلب
public function updateStatusByCustomer(Request $request, $id)
{
  $user = $request->user();
    if ($user->role !== 'customer') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $match = ServiceMatch::where('id', $id)
        ->where('customer_id', $user->id)
        ->where('status', 'pending')
        ->first();
    if (!$match) {
        return response()->json(['message' => 'Request not found or already decided'], 404);
    }
    $request->validate([
        'status' => 'required|in:accepted,rejected',
    ]);
    $match->status = $request->status;
    $match->save();
    $service = Service::find($match->service_id);
    if ($match->status === 'accepted') {
        $service->status = 'inprogress';
    } elseif ($match->status === 'rejected') {

        $service->status = 'pending';
    }
    $service->save();
    return response()->json([
        'message' => 'Request has been ' . $request->status,
        'match'   => $match
    ]);

}
    
 public function orderFinished(Request $request , $id ) 
{
    $user = $request->user();
    if ($user->role !== 'volunteer') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $match = ServiceMatch::where('id', $id)
        ->where('volunteer_id', $user->id) 
        ->where('status', 'accepted')
        ->first();
    if (!$match) {
        return response()->json(['message' => 'Request not found or already decided'], 404);
    }
    $request->validate([
        'status' => 'required|in:completed',
    ]);
    $match->update([
        'status' => $request->status
    ]);
    $service = Service::where('id', $match->service_id)->first();
  
    $service->status = 'finished';
    $service->save(); 
    return response()->json([
        'message' => 'Request has been ' . $request->status,
        'match' => $match
    ]);
   
}

}
