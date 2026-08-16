<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class OperationalAlert extends Notification
{
    use Queueable;

    public function __construct(private readonly string $key, private readonly string $title, private readonly string $message, private readonly string $status) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['key' => mb_substr($this->key, 0, 100), 'title' => mb_substr($this->title, 0, 120), 'message' => mb_substr($this->message, 0, 300), 'status' => $this->status];
    }
}
