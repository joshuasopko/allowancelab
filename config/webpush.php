<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAPID Keys for Web Push
    |--------------------------------------------------------------------------
    |
    | Generate keys via: php artisan tinker
    |   use Minishlink\WebPush\VAPID;
    |   $keys = VAPID::createVapidKeys();
    |
    */

    'vapid' => [
        'subject'     => env('VAPID_SUBJECT', 'mailto:hello@allowancelab.com'),
        'public_key'  => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Notification Preferences for Parents
    |--------------------------------------------------------------------------
    | push: bool — send browser push notification
    | email: bool — send email notification
    | threshold: float|null — minimum dollar amount to trigger (for money events)
    */

    'parent_defaults' => [
        'goal_created'                 => ['push' => false, 'email' => false],
        'goal_redemption_requested'    => ['push' => false, 'email' => true],
        'kid_deposited'                => ['push' => false, 'email' => false, 'threshold' => 20.00],
        'kid_spent'                    => ['push' => false, 'email' => false, 'threshold' => 20.00],
        'goal_completed'               => ['push' => false, 'email' => false],
        'allowance_processed'          => ['push' => false, 'email' => false],
        'points_low_warning'           => ['push' => false, 'email' => false],
        'wish_created'                 => ['push' => false, 'email' => false],
        'wish_purchase_requested'      => ['push' => false, 'email' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Notification Preferences for Kids (push only)
    |--------------------------------------------------------------------------
    */

    'kid_defaults' => [
        'money_added'        => ['push' => true],
        'money_deducted'     => ['push' => true],
        'allowance_received' => ['push' => true],
        'allowance_denied'   => ['push' => true],
        'points_adjusted'    => ['push' => true],
        'goal_approved'      => ['push' => true],
        'goal_denied'        => ['push' => true],
        // Wish events (wired when wish feature ships)
        'wish_approved'      => ['push' => true],
        'wish_denied'        => ['push' => true],
    ],

];
