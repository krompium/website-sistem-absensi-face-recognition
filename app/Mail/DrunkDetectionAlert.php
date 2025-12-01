<?php

// ================================================================

// app/Mail/DrunkDetectionAlert.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DrunkDetectionAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $detection;
    public $student;

    public function __construct($detection, $student)
    {
        $this->detection = $detection;
        $this->student = $student;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ ALERT: Deteksi Mabuk - ' . $this->student->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.drunk-detection-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
