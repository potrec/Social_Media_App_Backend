<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;



class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', 
        ['except' => ['login','register']]);
    }
    
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
        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'token' => $token,
        ]);
        //return $validatedData;

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
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
        $user = Auth::user();
        return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token,
            ]);
        // $user->tokens()->where('tokenable_id', $user['id'])->delete();
        // $token = $user->createToken('token')->plainTextToken;
        // $response = [
        //     'token' => $token,
        //     'user' => $user
        // ];
        // return $response;
    }
    public function logout(Request $request)
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }
    public function user()
    {
        if(Auth::guest())
        {
            return redirect()->route('/login');
        }
        return Auth::user();
    }
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
