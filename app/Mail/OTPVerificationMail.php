<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTPVerificationMail extends Mailable // implements ShouldQueue
{
    // use Queueable, SerializesModels;

    public $otp;
    public $fullName;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $fullName)
    {
        $this->otp = $otp;
        $this->fullName = $fullName;
        
        // Set queue priority and delay for production - DISABLED FOR DEBUGGING
        // $this->onQueue('emails');
        // $this->delay(now()->addSeconds(5)); // Small delay to prevent spam
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Kode Verifikasi Email - UDKS',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.otp-verification',
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