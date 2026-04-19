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
                'stars'   => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500',
            ]);

            $service_match = ServiceMatch::findOrFail($servicematch_id);
            $service       = Service::find($service_match->service_id);

            // تأكد إن المستخدم هو الـ customer
            if ($service_match->customer_id != $user->id) {
                return response()->json(['message' => 'Unauthorized to rate this service'], 403);
            }

            // تأكد إن الخدمة خلصت
            if ($service_match->status !== 'completed') {
                return response()->json(['message' => 'Service not completed yet', 'status' => $service_match->status], 403);
            }

            // ✅ إنشاء الـ rating
            $rating = Rating::create([
                'user_id'         => $user->id,
                'servicematch_id' => $servicematch_id,
                'stars'           => $request->stars,
                'comment'         => $request->comment,
            ]);

            // ✅ تحديث تقييم المتطوع
            $volunteer  = User::find($service_match->volunteer_id);
            $old_count  = $volunteer->ratings_count;
            $old_total  = $volunteer->average_rating * $old_count;
            $new_total  = $old_total + $request->stars;
            $new_count  = $old_count + 1;
            $volunteer->average_rating = round($new_total / $new_count, 1);
            $volunteer->ratings_count  = $new_count;
            $volunteer->save();

            // ✅ تحديث status الـ match
            $service_match->update(['status' => 'Ratinged']);

            // ✅ تحديث status الـ service لو موجود
            if ($service) {
                $service->update(['status' => 'Ratinged']);
            }

            DB::commit();

            return response()->json([
                'message'        => 'Rating created successfully',
                'average_rating' => $volunteer->average_rating,
                'ratings_count'  => $volunteer->ratings_count,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Rating error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }
}