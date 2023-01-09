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
    
}
