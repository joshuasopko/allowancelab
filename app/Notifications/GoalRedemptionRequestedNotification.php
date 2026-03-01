<?php

namespace App\Notifications;

use App\Models\Goal;
use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalRedemptionRequestedNotification extends Notification
{
    public function __construct(
        protected Kid  $kid,
        protected Goal $goal
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        if ($notifiable->wantsPush('goal_redemption_requested') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->wantsEmail('goal_redemption_requested')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '🏁 ' . $this->kid->name . ' wants to redeem a goal!',
            'body'  => '"' . $this->goal->title . '" is complete — tap to review',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'goal-redeem-' . $this->goal->id,
            'url'   => '/kids/' . $this->kid->id . '/goals',
            'requireInteraction' => true,
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . ' wants to redeem their goal!')
            ->greeting('Goal redemption request!')
            ->line($this->kid->name . ' has completed their goal and is requesting redemption:')
            ->line('**' . $this->goal->title . '** — $' . number_format($this->goal->current_amount, 2) . ' saved')
            ->action('Review Goal', url('/kids/' . $this->kid->id . '/goals'))
            ->line('Approve or deny the redemption from the goals page.');
    }
}
