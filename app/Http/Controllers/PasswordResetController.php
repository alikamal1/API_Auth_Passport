<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\PasswordReset;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::Where('email', $request->email)->first();

        if(! $user) {
            return response()->json(['message','No user have this email address'], 404);
        }

        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $user->email,
            'token' => str_random(60)
        ]);

        if($user && $passwordReset) {
            $user->notify(new PasswordResetRequest($passwordReset->token));
        }

        return response()->json([
            'message' => 'We have e-mailed your password reset Link!'
        ], 200);

    }

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if(! $passwordReset) {
            return response()->json([
                'message' => 'invalid password reset token'
            ], 404);
        }

        if(Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => 'timeout for this password reset token'
            ], 404);
        }

        return response()->json($passwordReset);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);
        
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if(! $passwordReset) {
            return response()->json(['message' => 'Invaild password reset token'], 404);
        }

        $user=User::where('email', $passwordReset->email)->first();

        if(!$user) {
            return response()->json([
                'message' => 'We can not find user with that e-mail address'
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        $passwordReset->delete();

        $user->notify(new PasswordResetSuccess($passwordReset));

        return response()->json($user);

    }
}
