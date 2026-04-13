<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\ServiceMatch;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // 📩 Get Messages
    public function getMessages($matchId)
    {
        $user = Auth::user();

        $match = ServiceMatch::findOrFail($matchId);

        // تأكد إن اليوزر طرف في الماتش
        if ($user->id !== $match->customer_id && $user->id !== $match->volunteer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = Message::where('service_match_id', $matchId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
            'is_accepted' => $match->status === 'accepted',
            'remaining_messages' => 10, // عدلها بعدين لو عندك ليميت
            'match_status' => $match->status,
        ]);
    }

    // 📤 Send Message
    public function sendMessage(Request $request, $matchId)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $user = Auth::user();

        $match = ServiceMatch::findOrFail($matchId);

        if ($user->id !== $match->customer_id && $user->id !== $match->volunteer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'service_match_id' => $matchId,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        return response()->json($message);
    }

    // ✅ Mark as Done
    public function markDone($matchId)
    {
        $user = Auth::user();

        $match = ServiceMatch::findOrFail($matchId);

        // بس الفولنتير اللي يقدر يعمل Done
        if ($user->id !== $match->volunteer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $match->status = 'completed';
        $match->save();

        return response()->json(['message' => 'Service marked as completed']);
    }
}
