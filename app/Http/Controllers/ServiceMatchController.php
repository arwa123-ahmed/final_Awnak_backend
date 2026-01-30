<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceMatch;
use Illuminate\Support\Facades\DB;
class ServiceMatchController extends Controller
{
   // هون بنعمل الاوردر يا المتطوع بيتطوع لخدمه يا الكاستمر بيطلب خدمه 
    public function store(Request $request, $service_id)
    {
        $user = $request->user();
        $service = Service::findOrFail($service_id);
        if ($service->user_id == $user->id) {
            return response()->json([
                'message' => 'You cannot request your own service'
            ], 403);
        }
        if ($service->type === 'offer') {
            if ($user->role !== 'customer') {
                return response()->json(['message' => 'Only customers can request this service'], 403);
            }
            $customer_id = $user->id;
            $volunteer_id = $service->user_id;
        } else {  
            if ($user->role !== 'volunteer') {
                return response()->json(['message' => 'Only volunteers can apply to this service'], 403);
            }
            $volunteer_id = $user->id;
            $customer_id = $service->user_id;
        }
        $exists = ServiceMatch::where('service_id', $service->id)
            ->where('customer_id', $customer_id)
            ->where('volunteer_id', $volunteer_id)
            ->where('status', 'pending')
            ->first();
        if ($exists) {
            return response()->json([
                'message' => 'You already requested this service'
            ], 409);
        }
        $match = ServiceMatch::create([
            'service_id'   => $service->id,
            'customer_id'  => $customer_id,
            'volunteer_id' => $volunteer_id,
            'status'       => 'pending',
        ]);
        return response()->json([
            'message' => 'Service request sent successfully',
            'request' => $match
        ], 201);
    }

    //  يعني offer عرض الطلبات الي اجت من الكاستمر للفولنتير
public function volunteerRequests(Request $request)
{
    $user = $request->user();
    $serviceIds = Service::where('type', 'offer')->pluck('id');
    if ($user->role !== 'volunteer') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $requests = ServiceMatch::where('volunteer_id', $user->id)
        ->where('status', 'pending')
        ->whereIn('service_id', $serviceIds)
        ->get();
    return response()->json([
        'requests' => $requests
    ]);
}

//  يعني request عرض الطلبات الي اجت من الفولنتير للكاستمر
public function customerRequests(Request $request)
{
    $user = $request->user();
    $serviceIds = Service::where('type', 'request')->pluck('id');
    if ($user->role !== 'customer') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $requests = ServiceMatch::where('customer_id', $user->id)
        ->where('status', 'pending')
        ->whereIn('service_id', $serviceIds)
        ->get();
    return response()->json([
        'requests' => $requests
    ]);
}

// بعد ماتخلص الخدمه يحول الوقت
public function moneyTransfer($serviceMatchId){

    $match = ServiceMatch::findOrFail($serviceMatchId);
    if ($match->status !== 'completed') {
        return response()->json([
            'message' => 'Service not completed yet'
        ], 403);
    }

    $customer = $match->customer;   // علاقة belongsTo User
    $volunteer = $match->volunteer; // علاقة belongsTo User
    $amount = $match->service->timeSalary;


    // Transaction لضمان atomicity
    DB::transaction(function () use ($customer, $volunteer, $amount, $match) {
        $customer->balance -= $amount;
        $customer->save();

        $volunteer->balance += $amount;
        $volunteer->save();

    });

    return response()->json([
        'message' => 'Payment transferred successfully',
        'customer_balance' => $customer->balance,
        'volunteer_balance' => $volunteer->balance,
        'service_status' => $match->status
    ]);

}




                                         // Section Category Delivary !!!!!!!! 

//قرار المتطوع يقبل او يرفض الطلب
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
        $this->startTimer($match);
        $service->status = 'inprogress';
    } elseif ($match->status === 'rejected') {
        //التايمر هون حيوقف
        $service->end_time = null;
        $service->status = 'pending';
    }
    $service->save();
    return response()->json([
        'message' => 'Request has been ' . $request->status,
        'match'   => $match
    ]);
}
//تشغيل العداد
private function startTimer($match)
{
  $service = Service::where('id', $match->service_id)->first();
  $service->end_time = now()->addMinutes($service->timeSalary); 
  $service->save();

}
//بحالة وصل المتطوع قبل نهايه الوقت
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
    $service->end_time = now();
    $service->status = 'finished';
    $service->save(); 
    return response()->json([
        'message' => 'Request has been ' . $request->status,
        'match' => $match
    ]);
   
}
//بحالة خلص الوقت والمتطوع بدو يتاخر
public function volunteerDelay(Request $request, $id)
{
    $request->validate([
        'delay_minutes' => 'required|integer|min:1',
        'delay_reason'  => 'required|string|max:255',
    ]);
    $match = ServiceMatch::where('id', $id)
        ->where('volunteer_id', $request->user()->id)
        ->where('status', 'timeFinished')
        ->firstOrFail();
    $match->delay_minutes = $request->delay_minutes;
    $match->delay_reason  = $request->delay_reason;
    $match->status        = 'delayed'; 
    $match->save(); 
    $service = Service::where('id', $match->service_id)->first();
     $service->status = 'inprogress';
    $service->end_time = now()->addMinutes($match->delay_minutes);
    $service->save();
    return response()->json([
        'message' => 'Delay applied successfully',
        'new_end_time' => $service->end_time,
        'match' => $match
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
        $this->startTimer($match);
        $service->status = 'inprogress';
    } elseif ($match->status === 'rejected') {
        //التايمر هون حيوقف
        $service->end_time = null;
        $service->status = 'pending';
    }
    $service->save();
    return response()->json([
        'message' => 'Request has been ' . $request->status,
        'match'   => $match
    ]);

}
                                         // Done...... Section Category Delivary !!!!!!!! 

}