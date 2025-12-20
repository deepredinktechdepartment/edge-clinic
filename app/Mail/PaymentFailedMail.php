<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $patientName;
    public $appointmentUrl;

    public function __construct($patientName, $appointmentUrl)
    {
        $this->patientName = $patientName;
        $this->appointmentUrl = $appointmentUrl;
    }

    public function build()
    {
        return $this->subject('Payment Failed – Book Your Appointment Again')
                    ->markdown('emails.payment_failed')
                    ->with([
                        'name' => $this->patientName,
                        'appointmentUrl' => $this->appointmentUrl,
                    ]);
    }
}