<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\VerificationCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 8;
    const LOCKOUT_DURATION = 3; // minutes

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'role'     => 'required|integer|in:1,2,3', // 1=admin, 2=employer, 3=applicant
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verificationCode = rand(100000, 999999);
        $rememberToken = Str::random(60);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_verified' => false,
            'verification_code' => $verificationCode,
            'remember_token' => Hash::make($rememberToken),
        ]);

        // Send verification email
        Mail::to($user->email)->send(new VerificationCode($verificationCode));

        return response()->json([
            'message' => 'User registered successfully. Check your email for the verification code.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role_name,
            ],
        ], 201);
    }

    /**
     * Verify email
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'verification_code' => 'required|string|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->verification_code !== $request->verification_code) {
            return response()->json(['error' => 'Invalid verification code.'], 400);
        }

        // Mark verified
        $user->is_verified = true;
        $user->verification_code = null;
        $user->email_verified_at = now();
        $user->remember_token = Hash::make(Str::random(60));
        $user->save();

        $token = Auth::login($user);
        $refreshToken = Str::random(60);
        $user->remember_token = Hash::make($refreshToken);
        $user->save();

        return $this->respondWithToken($token, $user, $refreshToken);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lockoutKey = 'login_attempts_' . $request->ip();
        $attempts = Cache::get($lockoutKey, 0);

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $remainingTime = Cache::get($lockoutKey . '_timer', self::LOCKOUT_DURATION * 60);
            return response()->json([
                'error' => 'Too many login attempts. Try again in ' . ceil($remainingTime / 60) . ' minutes.'
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Cache::put($lockoutKey, $attempts + 1, now()->addMinutes(self::LOCKOUT_DURATION));
            Cache::put($lockoutKey . '_timer', self::LOCKOUT_DURATION * 60, now()->addMinutes(self::LOCKOUT_DURATION));
            return response()->json(['error' => 'This email is not registered.'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            Cache::put($lockoutKey, $attempts + 1, now()->addMinutes(self::LOCKOUT_DURATION));
            Cache::put($lockoutKey . '_timer', self::LOCKOUT_DURATION * 60, now()->addMinutes(self::LOCKOUT_DURATION));

            $remainingAttempts = self::MAX_LOGIN_ATTEMPTS - ($attempts + 1);
            return response()->json([
                'error' => 'Incorrect password.',
                'remaining_attempts' => max(0, $remainingAttempts)
            ], 401);
        }

        if (!$user->is_verified) {
            $verificationCode = rand(100000, 999999);
            $user->verification_code = $verificationCode;
            $user->save();

            Mail::to($user->email)->send(new VerificationCode($verificationCode));

            return response()->json([
                'message' => 'Your email is not verified. A new verification code has been sent.',
            ], 403);
        }

        Cache::forget($lockoutKey);
        Cache::forget($lockoutKey . '_timer');

        $token = Auth::login($user);
        $refreshToken = Str::random(60);
        $user->remember_token = Hash::make($refreshToken);
        $user->save();

        return $this->respondWithToken($token, $user, $refreshToken);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->input('refresh_token'), $user->remember_token)) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }

        $newToken = Auth::refresh();
        $newRefreshToken = Str::random(60);
        $user->remember_token = Hash::make($newRefreshToken);
        $user->save();

        return $this->respondWithToken($newToken, $user, $newRefreshToken);
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        $user = Auth::user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_name' => $user->role_name,
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * JWT response helper
     */
    protected function respondWithToken($token, $user, $refreshToken = null)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_verified' => (bool)$user->is_verified,
                'role_name' => $user->role_name,
            ],
        ]);
    }
}
