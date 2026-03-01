<?php

namespace App\Notifications;

use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired when a kid requests to buy a wish from their wish list.
 * Wired in when the Wish feature ships.
 */
class WishPurchaseRequestedNotification extends Notification
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

        if ($notifiable->wantsPush('wish_purchase_requested') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->wantsEmail('wish_purchase_requested')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '🛒 ' . $this->kid->name . ' wants to buy something!',
            'body'  => '"' . $this->wishTitle . '" — $' . number_format($this->wishPrice, 2) . ' — tap to approve',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'wish-purchase-' . $this->wishId,
            'url'   => '/kids/' . $this->kid->id . '/wishes',
            'requireInteraction' => true,
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . ' wants to buy something!')
            ->greeting('Purchase request!')
            ->line($this->kid->name . ' is requesting to purchase **"' . $this->wishTitle . '"** for $' . number_format($this->wishPrice, 2) . '.')
            ->action('Review Request', url('/kids/' . $this->kid->id . '/wishes'))
            ->line('Approve or deny the request from the wish list page.');
    }
}
