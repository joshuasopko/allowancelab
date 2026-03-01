<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Notification;

/**
 * Shared helpers for all push-only (kid) notifications.
 * Subclasses must implement toWebPush().
 */
abstract class BaseWebPushNotification extends Notification
{
    public function via(mixed $notifiable): array
    {
        return [WebPushChannel::class];
    }
}
