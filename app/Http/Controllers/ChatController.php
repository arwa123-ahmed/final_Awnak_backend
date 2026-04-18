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

    // ─── GET messages ───
    public function getMessages(Request $request, $match_id)
    {
        $user  = $request->user();
        $match = ServiceMatch::findOrFail($match_id);

        $this->authorizeMatch($match, $user); // ✅ أول حاجة

        $messages     = Message::with('sender:id,name,id_image')
            ->where('service_match_id', $match_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $messageCount = $messages->count();

        // ✅ حالة واحدة بس — مش متكررة
        if ($match->status === 'accepted') {
            $remaining = null;
        } elseif ($match->status === 'completed') {
            $remaining = max(0, self::POST_COMPLETION_LIMIT - $match->post_completion_messages);
        } elseif ($match->status === 'inquiry') {
            $remaining = max(0, self::INQUIRY_LIMIT - $match->inquiry_messages);
        } else {
            // pending
            $remaining = max(0, self::MESSAGE_LIMIT - $messageCount);
        }

        return response()->json([
            'messages'          => $messages,
            'is_accepted'       => $match->status === 'accepted',
            'remaining_messages' => $remaining,
            'match_status'      => $match->status,
        ]);
    }

    // ─── POST send message ───
    public function sendMessage(Request $request, $match_id)
    {
        $user  = $request->user();
        $match = ServiceMatch::findOrFail($match_id);

        $this->authorizeMatch($match, $user); // ✅ أول حاجة

        $request->validate(['message' => 'required|string|max:1000']);

        // ✅ حالة واحدة بس — مش متكررة
        if ($match->status === 'accepted') {
            // unlimited — مفيش حاجة

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
            // pending
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

    // ─── PUT mark as done (volunteer only) ───
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

    // ─── POST start inquiry chat from profile ───
    public function startInquiry(Request $request, $volunteer_id)
    {
        $user = $request->user();

        // لو عنده match موجود مع الـ volunteer، افتحه
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