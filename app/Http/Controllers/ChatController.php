<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\ServiceMatch;

class ChatController extends Controller
{
    const MESSAGE_LIMIT = 30;
    const POST_COMPLETION_LIMIT = 30;
    const INQUIRY_LIMIT = 30;

    // ─── Helper: تأكد إن المستخدم طرف في الـ match ───
    private function authorizeMatch(ServiceMatch $match, $user)
    {
        if ($match->volunteer_id !== $user->id && $match->customer_id !== $user->id) {
            abort(response()->json(['message' => 'Unauthorized'], 403));
        }
    }

    //
    public function getMessages(Request $request, $match_id)
    {
        $user  = $request->user();
        $currentMatch = ServiceMatch::findOrFail($match_id);

        $this->authorizeMatch($currentMatch, $user);

        // تحديد الطرفين في المحادثة الحالية
        $userA = $currentMatch->customer_id;
        $userB = $currentMatch->volunteer_id;

        // 1. جلب كل الـ IDs لعمليات الربط (Matches) اللي تمت بين الشخصين دول "في العموم"
        $allMatchIds = ServiceMatch::where(function ($q) use ($userA, $userB) {
            $q->where('customer_id', $userA)->where('volunteer_id', $userB);
        })
            ->orWhere(function ($q) use ($userA, $userB) {
                $q->where('customer_id', $userB)->where('volunteer_id', $userA);
            })
            ->pluck('id');

        // 2. جلب كل الرسائل المرتبطة بكل الـ Matches دي
        $messages = Message::with('sender:id,name,id_image')
            ->whereIn('service_match_id', $allMatchIds) // استخدام whereIn بدل where
            ->orderBy('created_at', 'asc')
            ->get();

        // --- حساب الرسائل المتبقية (يظل مرتبطاً بالـ Match الحالي فقط) ---
        $messageCountInCurrentMatch = Message::where('service_match_id', $match_id)->count();

        if ($currentMatch->status === 'accepted') {
            $remaining = null;
        } elseif ($currentMatch->status === 'completed') {
            $remaining = max(0, self::POST_COMPLETION_LIMIT - $currentMatch->post_completion_messages);
        } elseif ($currentMatch->status === 'inquiry') {
            $remaining = max(0, self::INQUIRY_LIMIT - $currentMatch->inquiry_messages);
        } else {
            $remaining = max(0, self::MESSAGE_LIMIT - $messageCountInCurrentMatch);
        }

        return response()->json([
            'messages'           => $messages,
            'is_accepted'        => $currentMatch->status === 'accepted',
            'remaining_messages' => $remaining,
            'match_status'       => $currentMatch->status,
        ]);
    }

    //
    public function sendMessage(Request $request, $match_id)
    {
        $user  = $request->user();
        $match = ServiceMatch::findOrFail($match_id);

        $this->authorizeMatch($match, $user);

        $request->validate(['message' => 'required|string|max:1000']);

        //
        if ($match->status === 'accepted') {
        } elseif ($match->status === 'completed') {
            if ($match->post_completion_messages >= self::POST_COMPLETION_LIMIT) {
                return response()->json(['message' => 'Post-completion limit reached.', 'limit_reached' => true], 403);
            }
            $match->increment('post_completion_messages');
        } elseif ($match->status === 'inquiry') {
            if ($match->inquiry_messages >= self::INQUIRY_LIMIT) {
                return response()->json(['message' => 'Inquiry limit reached.', 'limit_reached' => true], 403);
            }
            $match->increment('inquiry_messages');
        } else {

            $count = Message::where('service_match_id', $match_id)->count();
            if ($count >= self::MESSAGE_LIMIT) {
                return response()->json(['message' => 'Message limit reached.', 'limit_reached' => true], 403);
            }
        }

        $msg = Message::create([
            'service_match_id' => $match_id,
            'sender_id'        => $user->id,
            'message'          => $request->message,
        ]);

        $msg->load('sender:id,name,id_image');

        return response()->json(['message' => $msg], 201);
    }

    //
    public function markDone(Request $request, $match_id)
    {
        $user  = $request->user();
        $match = ServiceMatch::findOrFail($match_id);

        if ($match->volunteer_id !== $user->id) {
            return response()->json(['message' => 'Only volunteer can mark as done'], 403);
        }

        if ($match->status !== 'accepted') {
            return response()->json(['message' => 'Match is not accepted yet'], 403);
        }

        $match->update([
            'status'                   => 'completed',
            'post_completion_messages' => 0,
        ]);

        return response()->json(['message' => 'Marked as done successfully']);
    }


    public function startInquiry(Request $request, $volunteer_id)
    {
        $user = $request->user();


        $existing = ServiceMatch::where('customer_id', $user->id)
            ->where('volunteer_id', $volunteer_id)
            ->whereIn('status', ['inquiry', 'pending', 'accepted', 'completed'])
            ->latest()
            ->first();

        if ($existing) {
            return response()->json(['match_id' => $existing->id]);
        }

        $match = ServiceMatch::create([
            'customer_id'      => $user->id,
            'volunteer_id'     => $volunteer_id,
            'status'           => 'inquiry',
            'inquiry_messages' => 0,
        ]);

        return response()->json(['match_id' => $match->id]);
    }
}
