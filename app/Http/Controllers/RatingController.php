<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceMatch;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function store(Request $request, $servicematch_id)
    {
        DB::beginTransaction();
        try {
            $user = $request->user(); // الشخص اللي فاتح الأبلكيشن (أنا)
            $service_match = ServiceMatch::findOrFail($servicematch_id);

            // تحديد الأطراف
            $currentUserId = (int) $user->id;
            $customerId = (int) $service_match->customer_id;
            $volunteerId = (int) $service_match->volunteer_id;

            // الطرف التاني (الهدف)
            $targetUserId = ($currentUserId === $customerId) ? $volunteerId : $customerId;

            // التأكد من البيانات
            $request->validate([
                'stars' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500',
            ]);

            // حفظ التقييم "للطرف التاني"
            // ركز هنا: لازم نبعت الـ user_id هو الـ $targetUserId عشان التقييم يروحله هو
            $rating = Rating::create([
                'user_id'         => $targetUserId,    // التقييم هينزل في بروفايل مين؟ (الطرف التاني)
                'reviewer_id'     => $currentUserId,   // مين اللي كتب التقييم؟ (أنا) - لازم تضيف العمود ده في الـ Migration
                'servicematch_id' => $servicematch_id,
                'stars'           => $request->stars,
                'comment'         => $request->comment,
            ]);

            // تحديث بيانات الشخص التاني
            $targetUser = User::find($targetUserId);
            $old_count = $targetUser->ratings_count ?? 0;
            $old_total = ($targetUser->average_rating ?? 0) * $old_count;

            $new_count = $old_count + 1;
            $new_total = $old_total + $request->stars;

            $targetUser->average_rating = round($new_total / $new_count, 1);
            $targetUser->ratings_count = $new_count;
            $targetUser->save();

            $service_match->update(['status' => 'Ratinged']);

            DB::commit();
            return response()->json(['message' => 'Rating submitted for the other party!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => "Failed to submit rating."], 500);
        }
    }
}
