<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RechargeBalance;
use App\Models\User;
use App\Notifications\BalanceRecharged;

class RechargeBalanceController extends Controller
{
    // ── Customer: رفع طلب شحن ──
    public function store(Request $request)
    {
        $user = $request->user();

        // ✅ لازم يكون activated
        if (!$user->activation) {
            return response()->json(['message' => 'Account not activated yet.'], 403);
        }

        if (!$request->hasFile('image')) {
            return response()->json(['message' => 'Screenshot is required.'], 400);
        }

        $request->validate([
            'amount' => 'required|in:199,499,999',
            'image'  => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store('recharge_images', 'public');

        $recharge = RechargeBalance::create([
            'user_id' => $user->id,
            'image'   => $path,
            'amount'  => $request->amount,
            'status'  => 'pending',
        ]);

        return response()->json(['message' => 'Recharge request submitted.', 'data' => $recharge], 201);
    }

    // ── Admin: عرض كل الطلبات ──
    public function index()
    {
        $recharges = RechargeBalance::with('user:id,name,email,activation')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($recharges);
    }

    // ── Admin: approve ──
  public function approve(Request $request, $id)
{
    $recharge = RechargeBalance::findOrFail($id);

    if ($recharge->status !== 'pending') {
        return response()->json(['message' => 'Already processed.'], 400);
    }

    $amount = $request->input('amount');
    
    if (!$amount || !in_array($amount, [400, 800, 2000])) {
        return response()->json(['message' => 'Invalid amount.'], 422);
    }

    $recharge->user->increment('balance', $amount);
    $recharge->update(['status' => 'approved']);
    
//   $user->notify(new BalanceRecharged((int)$amount));
  
    $recharge->user->notifications()->create([
        'message' => "تم شحن رصيدك بنجاح بمبلغ {$amount} دقيقة 🎉",
        'type'    => 'recharge',
    ]);
    // $user->notify(new BalanceRecharged((int)$amount));

    return response()->json([
        'message'     => 'Approved and balance updated.',
        'new_balance' => $recharge->user->fresh()->balance,
    ]);
}

    // ── Admin: reject ──
    public function reject($id)
    {
        $recharge = RechargeBalance::findOrFail($id);
        $recharge->update(['status' => 'rejected']);

        $recharge->user->notifications()->create([
            'message' => "تم رفض طلب شحن الرصيد بمبلغ {$recharge->amount} EGP.",
            'type'    => 'recharge',
        ]);

        return response()->json(['message' => 'Rejected.']);
    }
}