<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;




class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);
        // $token = $user->create
    }
    public function login(Request $request)
    {
        if(!Auth::attempt($request->only('email', 'password'))){
            return response([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }
        else{
            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;
            
            $cookie = cookie('jwt', $token, 60 * 24); // 1 day

            return Response([
                'message' => 'Success'
            ])->withCookie($cookie);
        }

    }
    public function logout(Request $request)
    {
        $cookie = Cookie::forget('jwt');
        return Response([
            'message' => 'Success'
        ])->withCookie($cookie);
    }
    public function user()
    {
        return Auth::user();
    }
}
