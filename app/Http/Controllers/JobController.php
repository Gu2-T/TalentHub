<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\JobPost;

use Carbon\Carbon;

class JobController extends Controller

{

    // ✅ Create Job (Only verified Employers)

    public function store(Request $request)

    {

        $user = Auth::user();

        if ($user->role !== 2 || !$user->employerProfile || $user->employerProfile->verification_status !== 'verified') {

            return response()->json(['error' => 'Only verified employers can create jobs'], 403);

        }

        $request->validate([

            'title'       => 'required|string|max:255',

            'description' => 'required|string',

            'location'    => 'nullable|string|max:255',

            'salary'      => 'nullable|numeric',

            'deadline'    => 'required|date|after:today',

        ]);

        $job = JobPost::create([

            'user_id'     => $user->id,

            'title'       => $request->title,

            'description' => $request->description,

            'location'    => $request->location,

            'salary'      => $request->salary,

            'deadline'    => $request->deadline,

        ]);

        return response()->json([

            'message' => 'Job created successfully',

            'job'     => $job,

        ], 201);

    }

    // ✅ List all jobs

    public function index()

    {

        $user = Auth::user();

        if ($user->role == 3) {

            // Applicant → hide expired jobs

            $jobs = JobPost::with('employer:id,name,email')

                ->where(function ($query) {

                    $query->whereNull('deadline')

                          ->orWhere('deadline', '>=', Carbon::today());

                })

                ->latest()

                ->get();

        } elseif ($user->role == 2) {

            // Employer → show only their jobs

            $jobs = JobPost::with('employer:id,name,email')

                ->where('user_id', $user->id)

                ->latest()

                ->get();

        } else {

            // Admin → show all jobs

            $jobs = JobPost::with('employer:id,name,email')->latest()->get();

        }

        return response()->json($jobs);

    }

    // ✅ View single job

    public function show($id)

    {

        $user = Auth::user();

        $job = JobPost::with('employer:id,name,email')->find($id);

        if (!$job) {

            return response()->json(['error' => 'Job not found'], 404);

        }

        if ($user->role == 3) {

            // Applicant → block viewing expired jobs

            if ($job->deadline && $job->deadline < Carbon::today()) {

                return response()->json(['error' => 'This job is no longer available'], 403);

            }

        }

        if ($user->role == 2 && $job->user_id !== $user->id) {

            // Employer can only view their own jobs

            return response()->json(['error' => 'You are not allowed to view this job'], 403);

        }

        return response()->json($job);

    }

    // ✅ Employer deletes their own job

    public function destroy($id)

    {

        $user = Auth::user();

        $job = JobPost::find($id);

        if (!$job) {

            return response()->json(['error' => 'Job not found'], 404);

        }

        if ($job->user_id !== $user->id || $user->role !== 2) {

            return response()->json(['error' => 'You are not allowed to delete this job'], 403);

        }

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);

    }

    // ✅ Employer updates their job

    public function update(Request $request, $id)

    {

        $user = Auth::user();

        $job = JobPost::find($id);

        if (!$job) {

            return response()->json(['error' => 'Job not found'], 404);

        }

        if ($job->user_id !== $user->id || $user->role !== 2) {

            return response()->json(['error' => 'You are not allowed to update this job'], 403);

        }

        $request->validate([

            'title'       => 'sometimes|required|string|max:255',

            'description' => 'sometimes|required|string',

            'location'    => 'nullable|string|max:255',

            'salary'      => 'nullable|numeric',

            'deadline'    => 'nullable|date|after:today',

        ]);

        $job->update($request->only(['title', 'description', 'location', 'salary', 'deadline']));

        return response()->json([

            'message' => 'Job updated successfully',

            'job'     => $job,

        ]);

    }

}
