<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\EmployerProfile;

use App\Models\ApplicantProfile;

use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller

{

    /**

     * Create a profile based on user role (Employer or Applicant).

     */

    public function createProfile(Request $request)

    {

        $user = Auth::user();

        // 🔒 Prevent duplicate profile

        if (($user->role == 2 && $user->employerProfile) || ($user->role == 3 && $user->applicantProfile)) {

            return response()->json(['error' => 'Profile already exists.'], 400);

        }

        if ($user->role == 2) {

            // ✅ Employer profile

            $validatedData = $request->validate([

                'company_name' => 'required|string|max:255',

                'tin_number'   => 'required|string|max:50|unique:employer_profiles',

                'address'      => 'required|string|max:255',

                'phone_number' => 'required|string|max:20',

                'website'      => 'nullable|url|max:255',

                'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            ]);

            // Upload logo

            $validatedData['image'] = $request->file('image')->store('employer_images', 'public');

            $validatedData['user_id'] = $user->id;

            $validatedData['verification_status'] = 'pending';

            $profile = EmployerProfile::create($validatedData);

            $profile = $this->formatProfileResponse($profile, 'employer');

        } elseif ($user->role == 3) {

            // ✅ Applicant profile

            $validatedData = $request->validate([

                'first_name'   => 'required|string|max:255',

                'last_name'    => 'required|string|max:255',

                'phone_number' => 'required|string|max:20',

                'address'      => 'nullable|string|max:255',

                'resume'       => 'required|file|mimes:pdf,doc,docx|max:4096',

                'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            ]);

            // Upload resume

            $validatedData['resume'] = $request->file('resume')->store('resumes', 'public');

            // Upload optional image

            if ($request->hasFile('image')) {

                $validatedData['image'] = $request->file('image')->store('applicant_images', 'public');

            }

            $validatedData['user_id'] = $user->id;

            $profile = ApplicantProfile::create($validatedData);

            $profile = $this->formatProfileResponse($profile, 'applicant');

        } else {

            return response()->json(['error' => 'Invalid role.'], 400);

        }

        return response()->json([

    'message' => $user->role == 2 

        ? 'Profile created successfully and awaiting verification' 

        : 'Profile created successfully',

    'profile' => $profile,

], 201);

    }

    public function updateProfile(Request $request)

{

    $user = Auth::user();

    if ($user->role == 2) {

        // ✅ Employer profile

        $profile = $user->employerProfile;

        if (!$profile) {

            return response()->json(['error' => 'Employer profile not found.'], 404);

        }

        $validatedData = $request->validate([

            'company_name' => 'sometimes|string|max:255',

            'tin_number'   => 'sometimes|string|max:50|unique:employer_profiles,tin_number,' . $profile->id,

            'address'      => 'sometimes|string|max:255',

            'phone_number' => 'sometimes|string|max:20',

            'website'      => 'nullable|url|max:255',

            'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

        ]);

        if ($request->hasFile('image')) {

            // delete old file

            if ($profile->image) {

                Storage::disk('public')->delete($profile->image);

            }

            $validatedData['image'] = $request->file('image')->store('employer_images', 'public');

        }

        // 🔄 Reset verification status

        $validatedData['verification_status'] = 'pending';

        $profile->update($validatedData);

        $profile = $this->formatProfileResponse($profile, 'employer');

    } elseif ($user->role == 3) {

        // ✅ Applicant profile

        $profile = $user->applicantProfile;

        if (!$profile) {

            return response()->json(['error' => 'Applicant profile not found.'], 404);

        }

        $validatedData = $request->validate([

            'first_name'   => 'sometimes|string|max:255',

            'last_name'    => 'sometimes|string|max:255',

            'phone_number' => 'sometimes|string|max:20',

            'address'      => 'nullable|string|max:255',

            'resume'       => 'nullable|file|mimes:pdf,doc,docx|max:4096',

            'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

        ]);

        if ($request->hasFile('resume')) {

            if ($profile->resume) {

                Storage::disk('public')->delete($profile->resume);

            }

            $validatedData['resume'] = $request->file('resume')->store('resumes', 'public');

        }

        if ($request->hasFile('image')) {

            if ($profile->image) {

                Storage::disk('public')->delete($profile->image);

            }

            $validatedData['image'] = $request->file('image')->store('applicant_images', 'public');

        }

        $profile->update($validatedData);

        $profile = $this->formatProfileResponse($profile, 'applicant');

    } else {

        return response()->json(['error' => 'Invalid role.'], 400);

    }

   return response()->json([

    'message' => $user->role == 2 

        ? 'Profile updated successfully and awaiting verification' 

        : 'Profile updated successfully',

        'profile' => $profile,

    ]);

}

    /**

     * Retrieve the authenticated user's profile.

     */

    public function getProfile()

    {

        $user = Auth::user();

        $profile = ($user->role == 2) ? $user->employerProfile : ($user->role == 3 ? $user->applicantProfile : null);

        if (!$profile) {

            return response()->json(['message' => 'No profile found.'], 404);

        }

        $formatted = $this->formatProfileResponse($profile, $user->role == 2 ? 'employer' : 'applicant');

        return response()->json($formatted);

    }

    /**

     * Serve stored files (images, resumes) through API.

     */

    public function getPhoto($folder, $filename)

    {

        $allowedFolders = ['employer_images', 'applicant_images', 'resumes'];

        if (!in_array($folder, $allowedFolders)) {

            return response()->json(['message' => 'Invalid folder name'], 400);

        }

        $path = storage_path("app/public/{$folder}/{$filename}");

        if (!file_exists($path)) {

            return response()->json(['message' => 'File not found'], 404);

        }

        return response()->download($path);

    }

    /**

     * Format profile response with proper file URLs.

     */

private function formatProfileResponse($profile, $type)

{

    $getFileResponse = function ($filename, $folder) {

        return [

            'path' => $filename,

            'url'  => $filename ? url("api/photo/{$folder}/" . basename($filename)) : null,

        ];

    };

    if ($type === 'employer') {

        $profile->image_path = $profile->image;

        $profile->image_url  = $getFileResponse($profile->image, 'employer_images')['url'];

        unset($profile->image);

    }

    if ($type === 'applicant') {

        $profile->resume_path = $profile->resume;

        $profile->resume_url  = $getFileResponse($profile->resume, 'resumes')['url'];

        unset($profile->resume);

        if ($profile->image) {

            $profile->image_path = $profile->image;

            $profile->image_url  = $getFileResponse($profile->image, 'applicant_images')['url'];

            unset($profile->image);

        }

    }

    return $profile;

}

}