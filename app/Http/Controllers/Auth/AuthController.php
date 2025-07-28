<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmailVerification;
use App\Mail\OTPVerificationMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Pengecer', // Assign the 'Pengecer' role
        ]);

        // Generate and send OTP for email verification
        $verification = EmailVerification::createForEmail($request->email);
        
        try {
            Mail::to($request->email)->send(new OTPVerificationMail($verification->otp, $user->full_name));
            
            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil. Kode OTP telah dikirim ke email Anda untuk verifikasi.',
                'data' => $user,
                'requires_verification' => true,
                'expires_at' => $verification->expires_at
            ], 201);
        } catch (\Exception $e) {
            // If email sending fails, still return success but indicate email issue
            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil, namun gagal mengirim email verifikasi. Silakan gunakan fitur kirim ulang OTP.',
                'data' => $user,
                'requires_verification' => true,
                'email_sent' => false
            ], 201);
        }
    }

    public function login(Request $request)
    {
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        // Check if email is verified (only for pengecer role)
        if (in_array($user->role, ['Pengecer', 'pengecer']) && !$user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email belum diverifikasi. Silakan verifikasi email Anda terlebih dahulu.',
                'requires_verification' => true,
                'email' => $user->email
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
} 