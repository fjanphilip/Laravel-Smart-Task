<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationController extends Controller
{
    public function stream(Request $request): StreamedResponse
    {
        // Menonaktifkan batas waktu eksekusi agar stream tidak mati karena timeout
        set_time_limit(0);

        $userId = $request->user()->id;

        $response = new StreamedResponse(function () use ($userId) {
            // Melacak ID notifikasi yang sudah dikirim selama koneksi aktif
            // agar tidak terjadi loop pengiriman data berulang
            $sentIds = [];

            while (true) {
                // Hentikan eksekusi jika browser/klien memutuskan koneksi (mencegah proses zombie)
                if (connection_aborted()) {
                    break;
                }

                // Kirim komentar keepalive untuk menjaga koneksi dan memicu deteksi pemutusan koneksi klien
                echo ": keepalive\n\n";
                ob_flush();
                flush();

                $notifications = Notification::with('task')
                    ->where('user_id', $userId)
                    ->where('is_read', false)
                    ->whereNotIn('id', $sentIds)
                    ->get();

                if ($notifications->isNotEmpty()) {
                    echo "data: " . json_encode($notifications) . "\n\n";

                    ob_flush();
                    flush();

                    // Masukkan ID yang sudah dikirim ke array pelacak
                    $sentIds = array_merge($sentIds, $notifications->pluck('id')->toArray());
                }

                sleep(3);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
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
