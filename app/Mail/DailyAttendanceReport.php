<?php
// app/Mail/DailyAttendanceReport.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class DailyAttendanceReport extends Mailable
{
    use Queueable, SerializesModels;

    public $attendances;
    public $date;
    public $stats;

    public function __construct($attendances, $date, $stats)
    {
        $this->attendances = $attendances;
        $this->date = $date;
        $this->stats = $stats;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Kehadiran Harian - ' . $this->date->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-attendance-report',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}