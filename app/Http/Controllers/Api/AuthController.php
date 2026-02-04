<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\EmailOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * LOGIN API
     * POST /api/login
     */
    public function login(Request $request)
    {
       
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        // 🔐 Remove old tokens (recommended)
        // $user->tokens()->delete();

        // 🔑 Create new token
        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * LOGOUT API (optional but recommended)
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string|min:6',
        ]);

        $user = $request->user();

        // Check old password
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Old password is incorrect'
            ], 422);
        }
        if($request->old_password === $request->new_password){
            return response()->json([
                'status' => false,
                'message' => 'New password cannot be the same as old password'
            ], 422);
        }
        if($request->new_password !== $request->new_password_confirmation){
            return response()->json([
                'status' => false,
                'message' => 'New password and confirmation do not match'
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    public function register(Request $request)
    {
        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // ✅ Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee', // default role
        ]);

        // ✅ Create token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

     public function deleteAccount(Request $request)
    {
        $user = auth()->user();

        DB::beginTransaction();
        try {
            // Delete related records (adjust table names if needed)
            DB::table('attendances')->where('user_id', $user->id)->delete();
            DB::table('reward_wallets')->where('user_id', $user->id)->delete();
            DB::table('monthly_points')->where('user_id', $user->id)->delete();

            // Revoke all API tokens
            $user->tokens()->delete();

            // Soft delete user (recommended)
            
            $user->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Deletion failed'
            ], 500);
        }
    }
    public function sendOtp(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    $otp = rand(100000, 999999);

    // delete old OTPs
    DB::table('email_otps')->where('email', $request->email)->delete();

    DB::table('email_otps')->insert([
        'email' => $request->email,
        'otp' => $otp,
        'expires_at' => now()->addMinutes(5),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Mail::raw("Your verification OTP is: $otp", function ($msg) use ($request) {
    //     $msg->to($request->email)
    //         ->subject('Verify Your Email');
    // });
    Mail::to($request->email)->send(new EmailOtpMail($otp));


    return response()->json([
        'success' => true,
        'message' => 'OTP sent to your email',
    ]);
}
public function verifyOtp(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email',
        'password' => 'required',
        'otp' => 'required',
    ]);

    $record = DB::table('email_otps')
        ->where('email', $request->email)
        ->where('otp', $request->otp)
        ->where('expires_at', '>', now())
        ->first();

    if (!$record) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP',
        ], 400);
    }

    // create user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'employee',
        'email_verified_at' => now(),
    ]);

    DB::table('email_otps')->where('email', $request->email)->delete();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Email verified & registration completed',
        'token' => $token,
        'user' => $user,
    ]);
}

public function resendOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    // ❌ If user already exists → no OTP resend
    if (\App\Models\User::where('email', $request->email)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Email already registered. Please login.',
        ], 409);
    }

    // ⏱ Rate limit: allow resend only after 60 seconds
    $lastOtp = DB::table('email_otps')
        ->where('email', $request->email)
        ->orderBy('created_at', 'desc')
        ->first();

    if ($lastOtp && Carbon::parse($lastOtp->created_at)->diffInSeconds(now()) < 60) {
        return response()->json([
            'success' => false,
            'message' => 'Please wait before requesting another OTP.',
        ], 429);
    }

    // 🔢 Generate new OTP
    $otp = rand(100000, 999999);

    // ❌ Delete old OTPs
    DB::table('email_otps')->where('email', $request->email)->delete();

    // ✅ Store new OTP
    DB::table('email_otps')->insert([
        'email' => $request->email,
        'otp' => $otp,
        'expires_at' => now()->addMinutes(5),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 📧 Send OTP email
    Mail::to($request->email)->send(new EmailOtpMail($otp));

    return response()->json([
        'success' => true,
        'message' => 'OTP resent successfully to your email.',
    ]);
}
}
