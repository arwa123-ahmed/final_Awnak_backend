<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceMatch;
use App\Models\Rating;
use App\Models\User;
use App\Models\RechargeBalance;

class RechargeBalanceController extends Controller
{
   public function store(Request $request)
{
    if (!$request->hasFile('image')) {
        return response()->json([
            'message' => 'لم يتم الشحن',
        ], 400);
    }

    $user = $request->user();
  // هي مشان الصورة بنعمل امر php artisan storage:link 
    $path = $request->file('image')->store('recharge_images', 'public');

    $recharge = RechargeBalance::create([
        'user_id' => $user->id,
        'image'   => $path,
    ]);

    return response()->json([
        'message' => 'تم الشحن ',
        'data' => $recharge,
    ], 201);
}

}
