<?php

namespace App\Services\Notifications;

use Illuminate\Notifications\Notification;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

class WebPushChannel
{
    /**
     * Send the push notification to all subscriptions on the notifiable.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notifiable, 'pushSubscriptions')) {
            return;
        }

        $subscriptions = $notifiable->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        if (!method_exists($notification, 'toWebPush')) {
            return;
        }

        $payload = $notification->toWebPush($notifiable, $notification);

        if (!$payload) {
            return;
        }

        $auth = [
            'VAPID' => [
                'subject'    => config('webpush.vapid.subject'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);
        $webPush->setReuseVAPIDHeaders(true);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->public_key,
                'authToken'       => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
            ]);

            $webPush->queueNotification($subscription, json_encode($payload));
        }

        // Send all queued notifications and clean up expired subscriptions
        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $notifiable->pushSubscriptions()
                    ->where('endpoint', $report->getRequest()->getUri()->__toString())
                    ->delete();
            }
        }
    }
}
