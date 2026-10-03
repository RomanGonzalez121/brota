<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

/**
 * M6: the notification centre shared by every role.
 */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $onlyUnread = $request->query('ver') === 'sin-leer';

        $notifications = $request->user()->notifications()
            ->when($onlyUnread, fn ($query) => $query->whereNull('read_at'))
            ->limit(60)
            ->get();

        return view('notifications.index', [
            'groups' => $notifications->groupBy(fn (DatabaseNotification $notification) => match (true) {
                $notification->created_at->isToday() => 'Hoy',
                $notification->created_at->isYesterday() => 'Ayer',
                $notification->created_at->greaterThan(now()->subDays(7)) => 'Esta semana',
                default => 'Anteriores',
            }),
            'unread' => $request->user()->unreadNotifications()->count(),
            // What the unread ones are about, for the summary beside the list.
            'kinds' => $request->user()->unreadNotifications()->get()
                ->countBy(fn (DatabaseNotification $notification) => match ($notification->data['icon']) {
                    'receipt' => 'Pedidos nuevos',
                    'flag' => 'Denuncias del foro',
                    'bike' => 'Entregas y envíos',
                    default => 'Cambios de estado',
                }),
            'total' => $request->user()->notifications()->count(),
            'onlyUnread' => $onlyUnread,
        ]);
    }

    /**
     * Opening a notification reads it and takes the person to what it is about.
     */
    public function open(Request $request, string $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification);

        $notification->markAsRead();

        return redirect($notification->data['url']);
    }

    public function markAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Marcaste todas las notificaciones como leídas.');
    }
}
