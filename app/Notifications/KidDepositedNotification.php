<?php

namespace App\Notifications;

use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KidDepositedNotification extends Notification
{
    public function __construct(
        protected Kid    $kid,
        protected float  $amount,
        protected string $note = ''
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        $meetsThreshold = $notifiable->meetsThreshold('kid_deposited', $this->amount);

        if ($meetsThreshold && $notifiable->wantsPush('kid_deposited') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($meetsThreshold && $notifiable->wantsEmail('kid_deposited')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        $body = $this->kid->name . ' added $' . number_format($this->amount, 2) . ' to their account';
        if ($this->note) {
            $body .= ' — ' . $this->note;
        }

        return [
            'title' => '💰 ' . $this->kid->name . ' made a deposit',
            'body'  => $body,
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'kid-deposit-' . $this->kid->id,
            'url'   => '/dashboard',
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . ' added money to their account')
            ->greeting('Deposit recorded!')
            ->line($this->kid->name . ' self-reported a deposit of **$' . number_format($this->amount, 2) . '**.')
            ->when($this->note, fn($m) => $m->line('Note: ' . $this->note))
            ->action('View Dashboard', url('/dashboard'));
    }
}
