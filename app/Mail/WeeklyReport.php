<?php

// ================================================================

// app/Mail/WeeklyReport.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class WeeklyReport extends Mailable
{
    use Queueable, SerializesModels;

    public $reportData;
    public $startDate;
    public $endDate;
    public $pdfPath;

    public function __construct($reportData, $startDate, $endDate, $pdfPath = null)
    {
        $this->reportData = $reportData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->pdfPath = $pdfPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Mingguan Kehadiran - ' . $this->startDate->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-report',
        );
    }

    public function attachments(): array
    {
        if ($this->pdfPath) {
            return [
                Attachment::fromPath($this->pdfPath)
                    ->as('laporan-mingguan.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}