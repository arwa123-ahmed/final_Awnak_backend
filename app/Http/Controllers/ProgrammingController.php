<?php

namespace App\Http\Controllers;
use App\Models\Service;
use App\Models\ServiceMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\BalanceEarned;
use App\Notifications\BalanceDeducted;


class ProgrammingController extends Controller
{
    
    // public function updateStatusByVolunteer(Request $request, $id)
    // {
    //     $user = $request->user();
    //     if ($user->role !== 'volunteer') {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     $match = ServiceMatch::where('id', $id)
    //         ->where('volunteer_id', $user->id)
    //         ->where('status', 'pending')
    //         ->first();

    //     if (!$match) {
    //         return response()->json(['message' => 'Request not found or already decided'], 404);
    //     }

    //     $request->validate([
    //         'status' => 'required|in:accepted,rejected',
    //     ]);

    //     if ($request->status === 'rejected') {
    //         $match->status = 'rejected';
    //         $match->save();

    //         return response()->json([
    //             'message' => 'Request has been rejected',
    //             'match'   => $match
    //         ]);
    //     }

    //     $match->status = 'accepted';
    //     $match->save();

    //     $service = Service::find($match->service_id);
    //     $service->status = 'inprogress';
    //     $service->save();

    //     return response()->json([
    //         'message' => 'Request has been accepted',
    //         'match'   => $match
    //     ]);
    // }
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
        return response()->json(['message' => 'Request not found'], 404);
    }

    $request->validate(['status' => 'required|in:accepted,rejected']);

    if ($request->status === 'rejected') {
        $match->status = 'rejected';
        $match->save();
        return response()->json(['message' => 'Rejected', 'match' => $match]);
    }

    // ✅ Accept بس - مفيش خصم هنا
    $match->status = 'accepted';
    $match->save();

    $service = Service::find($match->service_id);
    if ($service) {
        $service->status = 'inprogress';
        $service->save();
    }

    return response()->json(['message' => 'Accepted', 'match' => $match]);
}

    
public function updateStatusByCustomer(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is a customer
        if ($user->role !== 'customer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Find the pending match request for this customer
        $match = ServiceMatch::where('id', $id)
            ->where('customer_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$match) {
            return response()->json(['message' => 'Request not found or already processed'], 404);
        }

        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $service = Service::find($match->service_id);

        // If customer rejects the volunteer
        if ($request->status === 'rejected') {
            $match->status = 'rejected';
            $service->status = 'pending';
            $match->save();
            $service->save();

            return response()->json([
                'message' => 'Request has been rejected',
                'match'   => $match
            ]);
        }

        // If customer accepts the volunteer (Transaction to handle balance)
        return DB::transaction(function () use ($match, $service, $user) {
            $amount = $service->timesalary;

            // Check if customer has enough balance
            if ($user->balance < $amount) {
                return response()->json([
                    'message'   => 'Insufficient balance. Please recharge your account.',
                    'required'  => $amount,
                    'available' => $user->balance
                ], 403);
            }

            // 1. Deduct minutes from customer balance
            $user->balance -= $amount;
            $user->save();

            // 2. Update match status to accepted
            $match->status = 'accepted';

            // IMPORTANT: We removed $match->deducted_amount because the column is missing in your DB
            $match->save();

            // 3. Update service status to in-progress
            $service->status = 'inprogress';
            $service->save();

            return response()->json([
                'message'     => 'Request has been accepted successfully',
                'match'       => $match,
                'new_balance' => $user->balance,
            ]);
        });
    }



//     public function orderFinished(Request $request, $id)
//     {
//         $user = $request->user();

//         // 1. Find the match record
//         // Ensure the current user is the volunteer and the service is currently 'accepted'
//         $match = ServiceMatch::where('id', $id)
//             ->where('volunteer_id', $user->id)
//             ->where('status', 'accepted')
//             ->first();

//         if (!$match) {
//             return response()->json([
//                 'message' => 'Service request not found or not in an accepted state'
//             ], 404);
//         }

//         return DB::transaction(function () use ($match, $user) {
//             //  Fetch the related service to retrieve the payment value (timesalary)
//             $service = \App\Models\Service::find($match->service_id);

//             //  Determine the amount
//             // We pull this directly from the service because the match column is currently empty
//             $amount = $service->timesalary ?? 0;

//             //  Add the balance to the volunteer
//             $user->earnedBalance += $amount;
//             $user->save();

//             //  Update the match status to completed
//             $match->status = 'completed';
//             $match->save();

//             //  Update the original service status
//             // Offers go back to 'pending' to be available again; requests are marked 'finished'
//             if ($service->type === 'offer') {
//                 $service->status = 'pending';
//             } else {
//                 $service->status = 'finished';
//             }
//             $service->save();
//  //  Return success response with updated balance data
//             return response()->json([
//                 'message' => 'Service completed successfully',
//                 'earned_balance' => $amount,
//                 'new_volunteering_balance' => $user->earnedBalance
//             ]);
//         });
//     }
public function orderFinished(Request $request, $id)
{
    $volunteer = $request->user();

    $match = ServiceMatch::where('id', $id)
        ->where('volunteer_id', $volunteer->id)
        ->where('status', 'accepted')
        ->first();

    if (!$match) {
        return response()->json(['message' => 'Service not found or not accepted'], 404);
    }

    return DB::transaction(function () use ($match, $volunteer) {
        $service  = Service::find($match->service_id);
        $amount   = $service->timesalary ?? 0;
        $customer = \App\Models\User::find($match->customer_id);

        // ✅ تحقق من رصيد العميل
        if ($customer->balance < $amount) {
            return response()->json([
                'message'   => 'Customer has insufficient balance',
                'required'  => $amount,
                'available' => $customer->balance,
            ], 403);
        }

        // ✅ خصم من العميل
        $customer->balance -= $amount;
        $customer->save();

        // ✅ إضافة للمتطوع
        $volunteer->earnedBalance += $amount;
        $volunteer->save();

        // ✅ إشعارات
        $volunteer->notify(new BalanceEarned((int)$amount, $service->name));
        $customer->notify(new BalanceDeducted((int)$amount, $service->name));

        // ✅ تحديث الـ status
        $match->status = 'completed';
        $match->save();

        if ($service->type === 'offer') {
            $service->status = 'pending';
        } else {
            $service->status = 'finished';
        }
        $service->save();

        return response()->json([
            'message'                  => 'Service completed successfully',
            'earned_balance'           => $amount,
            'new_volunteering_balance' => $volunteer->earnedBalance,
            'customer_new_balance'     => $customer->balance,
        ]);
    });
}
}