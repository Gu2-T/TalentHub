<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\EmployerProfile;

class AdminController extends Controller

{

    /**

     * List all pending employer profiles

     */

    public function pendingEmployers()

    {

        $user = Auth::user();

        if ($user->role !== 1) {

            return response()->json(['error' => 'Unauthorized'], 403);

        }

        $pending = EmployerProfile::where('verification_status', 'pending')

            ->with('user:id,name,email')

            ->get();

        return response()->json($pending);

    }

    /**

     * Approve employer profile

     */

    public function approveEmployer($id)

    {

        $user = Auth::user();

        if ($user->role !== 1) {

            return response()->json(['error' => 'Unauthorized'], 403);

        }

        $profile = EmployerProfile::find($id);

        if (!$profile) {

            return response()->json(['error' => 'Employer profile not found'], 404);

        }

        $profile->verification_status = 'verified';

        $profile->rejection_reason = null; // clear if previously rejected

        $profile->save();

        return response()->json(['message' => 'Employer profile approved successfully']);

    }

    /**

     * Reject employer profile with reason

     */

    public function rejectEmployer(Request $request, $id)

    {

        $user = Auth::user();

        if ($user->role !== 1) {

            return response()->json(['error' => 'Unauthorized'], 403);

        }

        $request->validate([

            'reason' => 'required|string|max:500',

        ]);

        $profile = EmployerProfile::find($id);

        if (!$profile) {

            return response()->json(['error' => 'Employer profile not found'], 404);

        }

        $profile->verification_status = 'rejected';

        $profile->rejection_reason = $request->reason;

        $profile->save();

        return response()->json([

            'message' => 'Employer profile rejected successfully',

            'reason'  => $profile->rejection_reason

        ]);

    }

}