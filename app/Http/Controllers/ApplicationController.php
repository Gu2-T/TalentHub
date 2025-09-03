<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\JobPost;
use App\Models\ApplicantProfile;   // ✅ add this
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationShortlistedMail;
use App\Mail\ApplicationRejectedMail;
use App\Mail\NewApplicationMail;


class ApplicationController extends Controller
{
    // ✅ Apply to a job
public function apply(Request $request, $jobId)
{
    $user = Auth::user();

    if ($user->role !== 3 || !$user->applicantProfile) {
        return response()->json(['error' => 'Only applicants can apply'], 403);
    }

    $job = JobPost::find($jobId);
    if (!$job) {
        return response()->json(['error' => 'Job not found'], 404);
    }

    // Prevent duplicate applications
    $exists = Application::where('job_id', $jobId)
        ->where('applicant_id', $user->applicantProfile->id)
        ->exists();

    if ($exists) {
        return response()->json(['error' => 'You already applied for this job'], 400);
    }

    $application = Application::create([
        'job_id' => $jobId,
        'applicant_id' => $user->applicantProfile->id,
        'status' => 'applied',
    ]);

    // ✅ Send email to employer
    Mail::to($job->employer->email)
        ->send(new NewApplicationMail($application));

    return response()->json([
        'message' => 'Application submitted successfully and email sent to employer',
        'application' => $application,
    ], 201);
}
   // shortlist
public function shortlist($id)
{
    $user = Auth::user();
    $application = Application::with(['job', 'applicant.user'])->find($id);

    if (!$application) {
        return response()->json(['error' => 'Application not found'], 404);
    }

    if ($user->role !== 2 || $application->job->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $application->status = 'shortlisted';
    $application->save();

    // send email
    Mail::to($application->applicant->user->email)
        ->send(new ApplicationShortlistedMail($application));

    return response()->json(['message' => 'Application shortlisted and email sent']);
}

// reject
public function reject($id)
{
    $user = Auth::user();
    $application = Application::with(['job', 'applicant.user'])->find($id);

    if (!$application) {
        return response()->json(['error' => 'Application not found'], 404);
    }

    if ($user->role !== 2 || $application->job->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $application->status = 'rejected';
    $application->save();

    // send email
    Mail::to($application->applicant->user->email)
        ->send(new ApplicationRejectedMail($application));

    return response()->json(['message' => 'Application rejected and email sent']);
}

}
