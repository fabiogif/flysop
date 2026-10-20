<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Feed de notificações do gestor no app — mesma tabela/regra do Admin\NotificationController.
 */
class NotificationApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        // notifications() já vem do mais recente para o mais antigo
        $notifications = $user->notifications()->paginate(20);

        return response()->json([
            'data' => collect($notifications->items())->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'data' => $notification->data,
                'read_at' => optional($notification->read_at)->toIso8601String(),
                'created_at' => $notification->created_at->toIso8601String(),
            ]),
            'meta' => [
                'unread_count' => $user->unreadNotifications()->count(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    public function read(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();

        return response()->json([], 204);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([], 204);
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json([], 204);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate(['notify_new_occurrences' => 'required|boolean']);

        $request->user()->update($data);

        return response()->json($data);
    }
}
