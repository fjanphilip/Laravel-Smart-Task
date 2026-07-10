<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::with('task')
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function readAll(Request $request)
    {
        Notification::where('user_id', $request->user()->id)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }
}
