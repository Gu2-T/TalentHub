<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function build()
    {
        return $this->subject('Update on your job application')
                    ->view('emails.application_rejected')
                    ->with([
                        'jobTitle' => $this->application->job->title,
                        'company'  => $this->application->job->employer->name,
                    ]);
    }
}
