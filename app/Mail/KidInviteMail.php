<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Kid;
use App\Models\Invite;

class KidInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $kid;
    public $invite;
    public $inviteUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Kid $kid, Invite $invite)
    {
        $this->kid = $kid;
        $this->invite = $invite;
        $this->inviteUrl = url('/invite/' . $invite->token);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re Invited to Join AllowanceLab!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.kid-invite',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}