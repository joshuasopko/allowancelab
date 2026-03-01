<?php

namespace App\Notifications;

use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KidSpentNotification extends Notification
{
    public function __construct(
        protected Kid    $kid,
        protected float  $amount,
        protected string $note = ''
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        $meetsThreshold = $notifiable->meetsThreshold('kid_spent', $this->amount);

        if ($meetsThreshold && $notifiable->wantsPush('kid_spent') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($meetsThreshold && $notifiable->wantsEmail('kid_spent')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        $body = $this->kid->name . ' spent $' . number_format($this->amount, 2);
        if ($this->note) {
            $body .= ' — ' . $this->note;
        }

        return [
            'title' => '🛍️ ' . $this->kid->name . ' recorded a purchase',
            'body'  => $body,
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'kid-spent-' . $this->kid->id,
            'url'   => '/dashboard',
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . ' recorded a purchase')
            ->greeting('Purchase recorded!')
            ->line($this->kid->name . ' self-reported spending **$' . number_format($this->amount, 2) . '**.')
            ->when($this->note, fn($m) => $m->line('Note: ' . $this->note))
            ->action('View Dashboard', url('/dashboard'));
    }
}
