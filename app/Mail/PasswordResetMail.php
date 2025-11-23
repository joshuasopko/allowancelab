<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Kid;
use App\Models\User;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $kid;
    public $parent;

    public function __construct(Kid $kid, User $parent)
    {
        $this->kid = $kid;
        $this->parent = $parent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Password Has Been Reset',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}