<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function build()
    {
        return $this->subject('New Job Application Received')
                    ->view('emails.new_application')
                    ->with([
                        'jobTitle' => $this->application->job->title,
                        'applicantName' => $this->application->applicant->first_name . ' ' . $this->application->applicant->last_name,
                    ]);
    }
}
