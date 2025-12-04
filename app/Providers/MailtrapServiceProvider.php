<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Mailtrap\Transport\MailtrapApiTransport;

class MailtrapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Mail::extend('mailtrap', function () {
            return new MailtrapApiTransport(
                config('services.mailtrap.api_key')
            );
        });
    }
}