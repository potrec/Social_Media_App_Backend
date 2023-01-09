<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;


class PostController extends Controller
{
    public function postCreatePost(Request $request)
    {
        // Validation

        $post = new Post();
        $post->messageContent = $request['messageContent'];
        $request->user()->posts()->save($post);
        return Response([
            'message' => 'Success'
        ]);
    }

    public function getPosts(Request $request)
    {
        $posts = DB::table('posts')->get();
        return $posts;
    }
}
