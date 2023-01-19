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

    // public function getPosts(Request $request)
    // {
    //     $posts = DB::table('posts')->get();
    //     return $posts;
    // }
    public function getPosts(Request $request)
    {
        $result = DB::table('posts')->join('users', 'users.id', '=', 'posts.user_id')->select('posts.id','users.name','posts.user_id','users.email','posts.parent_id','posts.messageContent','posts.created_at','posts.updated_at')->orderBy('posts.updated_at','desc')->get();
        return $result;
    }
    public function deletePost($id) {
        $post = Post::find($id);
        if($post) {
            if($post->user_id == auth()->user()->id) {
                $post->delete();
                return Response([
                    'message' => 'Success'
                ]);
            }
            return Response([
                'message' => 'Unauthorized'
            ], 401);
        }
        return Response([
            'message' => 'Post not found'
        ], 404);
    }

    public function updatePost(Request $request, $id) {
        $post = Post::find($id);
        if($post){
            if($post->user_id == auth()->user()->id) {
                $validatedData = $request->validate([
                    'messageContent' => 'required|string|max:126|min:6',
                ]);
                if ($request->validator && $request->validator->errors()) {
                    $errors = $request->validator->errors();
                    return response()->json(['errors' => $errors], 422);
                }
                else {
                $post->messageContent = $validatedData['messageContent'];
                $post->save();
                return response()->json(['message' => 'Success'], 200);
                }
            }
            return Response([
                'message' => 'Unauthorized'
            ], 401);
        }
        return Response([
            'message' => 'Post not found'
        ],404);
    
    }
    public function reactionLike(Request $request, $id){
        $post = Post::find($id);
        
    }
}
