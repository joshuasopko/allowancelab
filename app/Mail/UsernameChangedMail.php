<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Kid;
use App\Models\User;

class UsernameChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $kid;
    public $parent;
    public $oldUsername;
    public $newUsername;

    public function __construct(Kid $kid, User $parent, $oldUsername, $newUsername)
    {
        $this->kid = $kid;
        $this->parent = $parent;
        $this->oldUsername = $oldUsername;
        $this->newUsername = $newUsername;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Username Has Been Changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.username-changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}