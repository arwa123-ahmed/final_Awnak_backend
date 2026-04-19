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
        $user = $request->user();
        $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);
        $service_match = ServiceMatch::findOrFail($servicematch_id);
        $service = Service::find($service_match->service_id);
        // نتأكد من انو المستخدم هو الكاستمر
        if ($service_match->customer_id != $user->id) {
            return response()->json(['message' => 'غير مصرح لك بتقييم هذه الخدمة'], 403);
        }
        // نتأكد الاول اذا الخدمه خلصت
        if ($service_match->status !== 'completed') {
            return response()->json(['message' => 'Service not completed yet'], 403);
        }
    $rating = Rating::create([
    'user_id' => $user->id,
    'servicematch_id' => $servicematch_id,
    'stars' => $request->stars,
    'comment' => $request->comment,
]);

$volunteer = User::find($service_match->volunteer_id);
$old_count = $volunteer->ratings_count;
$old_total = $volunteer->average_rating * $old_count;
$new_total = $old_total + $request->stars;
$new_count = $old_count + 1;
$volunteer->average_rating = round($new_total / $new_count, 1);
$volunteer->ratings_count = $new_count;
$volunteer->save();

$service_match->update(['status' => 'Ratinged']);

// ✅ تحقق إن الـ service موجود قبل التحديث
if ($service) {
    $service->update(['status' => 'Ratinged']);
}
        DB::commit();
        return response()->json([
            'message' => 'Rating created successfully',
            'average_rating' => $volunteer->average_rating,
            'ratings_count' => $volunteer->ratings_count
        ], 201);
        } catch (\Exception $e) {// لو صار شي غلط نلغي الفانكشن كلها
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
        }
}

}
 