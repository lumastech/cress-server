<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class alertMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $data = [];
      public function __construct($alert)
    {
        $user = auth()->user();
        $this->data['name'] = $user->name;
        $this->data['email'] = $user->email;
        $this->data['phone'] = $user->phone;
        $this->data['alert'] = $alert->name;
        $this->data['message'] = $alert->message;
        $this->data['location'] = "https://www.google.com/maps/place/$alert->lat,$alert->lng";
    }

    public function build()
    {
        return $this->view('emails.alert')
                    ->with('data', $this->data)
                    ->subject('Subject');
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Emergence Message',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.alert',
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
