<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $pdfContent;
    public $filename;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $body
     * @param string $pdfContent
     * @param string $filename
     */
    public function __construct($subject, $body, $pdfContent, $filename)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->pdfContent = $pdfContent;
        $this->filename = $filename;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->html($this->body)
                    ->attachData($this->pdfContent, $this->filename, [
                        'mime' => 'application/pdf',
                    ]);
    }
}
