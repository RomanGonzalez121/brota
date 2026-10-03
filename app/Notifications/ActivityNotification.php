<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * A stored notification shown in the bell dropdown. There is no real-time
 * delivery: the list is read from the database on each page load.
 */
class ActivityNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $body,
        public string $url,
        public string $icon = 'bell',
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $this->icon,
        ];
    }
}
