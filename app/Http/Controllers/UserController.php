<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        return $request->user();
    }

    public function getUserNameById($id)
    {
        $user = User::where('id', $id)->first();
        return strip_tags($user['name']);
    }
    public function updateUser(Request $request)
    {
        $user = Auth::user();
        // Validate the incoming request data
        $validatedData = $request->validate([
            'email' => ['email', 'unique:users,email,' . $user['email']],
            'password' => 'required|string|confirmed|max:30|min:6',
            'name' => ['required', 'unique:users,name,' . $user['name']],
        ]);
        try {
            // Update the user data if provided
            if (isset($validatedData['email'])) {
                $user->email = $validatedData['email'];
            }
    
            if (isset($validatedData['password'])) {
                $user->password = bcrypt($validatedData['password']);
            }
    
            if (isset($validatedData['name'])) {
                $user->name = $validatedData['name'];
            }
    
            $user->save();
    
            return response()->json(['message' => 'User data updated successfully']);
        } catch (\Exception $e) {
            // Handle any errors that occur during the update
            return response()->json(['message' => 'Error updating user data: ' . $e->getMessage()], 500);
        }
    }
    
}
