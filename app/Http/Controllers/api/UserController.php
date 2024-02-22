<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */


     //Register and Sending verfication code
     public function register(Request $request)
     {
         $data = $request->validate([
             'email' => 'required|email|unique:users,email',
             'password' => 'required|string|min:6',
         ], [
             'email.unique' => 'This email address is already registered.',
             'password.min' => 'The password must be at least 6 characters long.',
         ]);
     
         $verificationCode = Str::random(6);
          
         $user = User::create([
             'email' => $request->email,
             'password' => Hash::make($data['password']),
             'verification_code' => $verificationCode,
             'verified' => false,
         ]);
     
         Mail::raw("Your verification code is: $verificationCode", function ($message) use ($data) {
             $message->to($data['email'])
                     ->subject('Verify Your Email');
         });
     
         return response()->json(['message' => 'Verification code sent successfully'], 200);
     }
     
      // Activate account using this code.
     public function activateAccount(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string',
        ]);
    
        $user = User::where('email', $data['email'])
                    ->where('verification_code', $data['verification_code'])
                    ->first();
    
        if ($user) {
            if ($user->verified) {
                return response()->json(['message' => 'Account already activated'], 400);
            }
    
            $user->verified = true;
            $user->save();
    
            return response()->json(['message' => 'Account activated successfully'], 200);
        }
    
        return response()->json(['error' => 'Invalid verification code'], 400);
    }
      
      // Get verified users.
    public function getAllUsers()
    {
        $verifiedUsers = User::where('verified', true)->pluck('email')->all();
    
        return response()->json(['users' => $verifiedUsers], 200);
    }



}
