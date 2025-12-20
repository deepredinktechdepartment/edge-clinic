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
    public $doctorName;
    public $appointmentDate;
    public $appointmentTime;

    /**
     * Create a new message instance.
     */
    public function __construct($patientName, $appointmentUrl, $doctorName, $appointmentDate, $appointmentTime)
    {
        $this->patientName = $patientName;
        $this->appointmentUrl = $appointmentUrl;
        $this->doctorName = $doctorName;
        $this->appointmentDate = $appointmentDate;
        $this->appointmentTime = $appointmentTime;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Payment Failed – Book Your Appointment Again')
                    ->markdown('emails.payment_failed')
                    ->with([
                        'name' => $this->patientName,
                        'appointmentUrl' => $this->appointmentUrl,
                        'doctorName' => $this->doctorName,
                        'appointmentDate' => $this->appointmentDate,
                        'appointmentTime' => $this->appointmentTime,
                    ]);
    }
}
