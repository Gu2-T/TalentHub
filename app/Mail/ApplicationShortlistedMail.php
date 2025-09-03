<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationShortlistedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function build()
    {
        return $this->subject('You have been shortlisted for an interview')
                    ->view('emails.application_shortlisted')
                    ->with([
                        'jobTitle' => $this->application->job->title,
                        'company'  => $this->application->job->employer->name,
                    ]);
    }
}
