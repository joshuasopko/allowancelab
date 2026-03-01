<?php

namespace App\Notifications;

use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired when a kid adds a new wish to their wish list.
 * Wired in when the Wish feature ships.
 */
class WishCreatedNotification extends Notification
{
    public function __construct(
        protected Kid    $kid,
        protected string $wishTitle,
        protected float  $wishPrice,
        protected int    $wishId
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        if ($notifiable->wantsPush('wish_created') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->wantsEmail('wish_created')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '⭐ ' . $this->kid->name . ' added a wish',
            'body'  => '"' . $this->wishTitle . '" — $' . number_format($this->wishPrice, 2),
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'wish-created-' . $this->wishId,
            'url'   => '/kids/' . $this->kid->id . '/wishes',
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . ' added a new wish')
            ->greeting('New wish added!')
            ->line($this->kid->name . ' just added **"' . $this->wishTitle . '"** ($' . number_format($this->wishPrice, 2) . ') to their wish list.')
            ->action('View Wish List', url('/kids/' . $this->kid->id . '/wishes'));
    }
}
