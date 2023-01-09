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
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed|max:30|min:6',
            'password_confirmation' => 'required|string'
        ]);
        if ($request->validator && $request->validator->errors()) {
            $errors = $request->validator->errors();
            $errorMessages = [];
            $passwordLength = strlen($validatedData['password']);
        
            if ($errors->has('name')) {
                $errorMessages['name'] = $errors->first('name');
            }
            if ($errors->has('email')) {
                $errorMessages['email'] = $errors->first('email');
            }
            if ($errors->has('password')) {
                $passwordError = $errors->first('password');
                if ($passwordError == 'The password confirmation does not match.') {
                    $errorMessages['password_confirmation'] = $passwordError;
                }
            }
            if (count($errorMessages) > 0) {
                return response()->json(['errors' => $errorMessages], 422);
            }

        }  
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password'])
        ]);
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json(['error' => 'Account with this email does not exist'], 404);
        }
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Wrong password'], 401);
        }
        $passwordLength = strlen($credentials['password']);
        if ($passwordLength < 6 || $passwordLength > 30) {
            return response()->json(['error' => 'The password must be between 6 and 30 characters'], 422);
        }
        $token = $user->createToken('token')->plainTextToken;
        // $cookie = cookie('jwt', $token, 60 * 24); // 1 day
        $response = [
            'token' => $token,
            'user' => $user
        ];
        return $response;

        // return Response([
        //     'message' => 'Success'
        // ])->withCookie($cookie);
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
        if(Auth::guest())
        {
            return redirect()->route('/login');
        }
        return Auth::user();
    }
}
