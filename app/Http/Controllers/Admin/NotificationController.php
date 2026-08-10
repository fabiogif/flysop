<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('admin.pages.notifications.index', compact('notifications'));
    }

    public function read(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();

            if (! empty($notification->data['url'])) {
                return redirect($notification->data['url']);
            }
        }

        return redirect()->route('notifications.index');
    }

    public function readAll(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')->with('messageSuccess', 'Notificações marcadas como lidas.');
    }
}
