<?php

namespace App\Notifications;

use App\Models\Goal;
use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalCompletedNotification extends Notification
{
    public function __construct(
        protected Kid  $kid,
        protected Goal $goal
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        if ($notifiable->wantsPush('goal_completed') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->wantsEmail('goal_completed')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '🎉 ' . $this->kid->name . ' reached their goal!',
            'body'  => '"' . $this->goal->title . '" is fully funded — $' . number_format($this->goal->target_amount, 2),
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'goal-complete-' . $this->goal->id,
            'url'   => '/kids/' . $this->kid->id . '/goals',
            'requireInteraction' => true,
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . ' reached their savings goal! 🎉')
            ->greeting('Goal reached!')
            ->line($this->kid->name . ' has fully funded their goal:')
            ->line('**' . $this->goal->title . '** — $' . number_format($this->goal->target_amount, 2))
            ->action('View Goal', url('/kids/' . $this->kid->id . '/goals'))
            ->line('They may now request redemption from their dashboard.');
    }
}
