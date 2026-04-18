<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type']    ?? 'general',
                'title'      => $n->data['title']   ?? '',
                'message'    => $n->data['message'] ?? '',
                'amount'     => $n->data['amount']  ?? null,
                'is_read'    => !is_null($n->read_at),
                'created_at' => $n->created_at,
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All marked as read']);
    }

    public function markRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);
        if ($notification) $notification->markAsRead();
        return response()->json(['message' => 'Marked as read']);
    }
}