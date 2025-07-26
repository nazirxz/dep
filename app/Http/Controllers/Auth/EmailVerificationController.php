<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OTPVerificationMail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak valid atau tidak terdaftar',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        // Check if user is already verified
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terverifikasi'
            ], 400);
        }

        // Generate and send OTP
        $verification = EmailVerification::createForEmail($request->email);
        
        try {
            Mail::to($request->email)->send(new OTPVerificationMail($verification->otp, $user->full_name));
            
            Log::info('OTP sent successfully', [
                'email' => $request->email,
                'otp_id' => $verification->id,
                'expires_at' => $verification->expires_at
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
                'expires_at' => $verification->expires_at
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. Silakan coba lagi.'
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $verification = EmailVerification::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('is_verified', false)
            ->first();

        if (!$verification) {
            Log::warning('Invalid OTP verification attempt', [
                'email' => $request->email,
                'otp' => $request->otp,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid'
            ], 400);
        }

        if ($verification->isExpired()) {
            Log::warning('Expired OTP verification attempt', [
                'email' => $request->email,
                'otp_id' => $verification->id,
                'expired_at' => $verification->expires_at
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kedaluwarsa'
            ], 400);
        }

        // Mark OTP as verified
        $verification->update(['is_verified' => true]);

        // Mark user email as verified
        $user = User::where('email', $request->email)->first();
        $user->update(['email_verified_at' => now()]);

        // Generate auth token
        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Email verification successful', [
            'email' => $request->email,
            'user_id' => $user->id,
            'otp_id' => $verification->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function resendOTP(Request $request)
    {
        return $this->sendOTP($request);
    }
}