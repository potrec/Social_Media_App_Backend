<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
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
        $result = DB::table('posts')->join('users', 'users.id', '=', 'posts.user_id')->select('posts.id','users.name','posts.user_id','users.email','posts.parent_id','posts.messageContent','posts.created_at','posts.updated_at')->orderBy('posts.updated_at','desc')->get();
        return $result;
    }
    public function deletePost($id) {
        $post = Post::find($id);
        if($post) {
            if($post->user_id == auth()->user()->id) {
                $user = Auth::user();
                $id_array = [$id];
                $like = DB::table('likes')->whereIn('post_id', $id_array)->delete();
                $comment = DB::table('comments')->whereIn('post_id', $id_array)->delete();
                $post->delete();
                return Response([
                    'message' => 'Success deleted:'
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
    public function likePost(Request $request){
        $post_id= $request['post_id'];
        $is_like= $request['like'];
        $update = false;
        $post = Post::find($post_id);
        if (!$post)
        {
            return response()->json(['message' => 'No post found'], 406);
        }
        $user = Auth::user();
        $like = $user->likes()->where('post_id', $post_id)->first();
        if($like)
        {
            $already_like = $like->like;
            $update = true;
            if ($already_like == $is_like)
            {
                $like->delete();
                return response()->json(['message' => 'Post reaction deleted'], 200);
            }
        }
        else
        {
            $like = new Like();
        }
        $like->like = $is_like;
        $like->user_id = $user->id;
        $like->post_id = $post->id;
        if($update)
        {
            $like->update();
            return response()->json(['message' => 'Post reaction updated'], 200);
        }
        else
        {
            $like->save();
            return response()->json(['message' => 'Post reaction created'], 200);
        }
        
    }
    public function countLikePosts(Request $request)
    {
        $post_id= $request['post_id'];
        $result_likes = DB::table('likes')->where('like', true)->where('post_id',$post_id)->count();
        $result_dislikes = DB::table('likes')->where('like', false)->where('post_id',$post_id)->count();
        return response()->json(['likes_count' => $result_likes,
                                 'dislikes_count' => $result_dislikes], 200);
    }
    public function isLikedByUser(Request $request)
    {
        $post_id = $request['post_id'];
        $user = Auth::user();
        $user_id = $user['id'];
        $checkTable = DB::table('likes')->where('post_id',$post_id)->where('user_id',$user_id)->first();
        if(!$checkTable)
        {
            return response()->json(['message' => 'post not found'], 200);
        }
        else
        {
            $like = $checkTable->like;
            if($like == 1)
            {
                return response()->json(['message' => 1], 200);
            }
            else
            {
                return response()->json(['message' => 0],200);
            }
        }
        return response()->json(['message' => $checkTable],200);
    }
    public function createPostComment(Request $request)
    {
        $post_id = $request['post_id'];
        $messageContent = $request['messageContent'];
        if($messageContent == '')
        {
            return response()->json(['error' => 'no text in comment'],400);
        }
        $user = Auth::user();
        $comment = new Comment;
        $comment-> user_id = $user->id;
        $comment-> post_id = $post_id;
        $comment-> messageContent = $messageContent;
        $comment->save();
        return response()->json(['message' => $comment],200);

    }
    public function getComments($id)
    {
        $result = DB::table('comments')->join('users', 'users.id', '=', 'comments.user_id')
        ->select('comments.id','comments.post_id','users.name','comments.user_id','users.email','comments.messageContent','comments.created_at','comments.updated_at')->where('comments.post_id',$id)->orderBy('comments.created_at','asc')->get();
        return $result;
    }
    public function getCommentsCount($id)
    {
        return $result = DB::table('comments')->where('post_id',$id)->orderBy('created_at','asc')->count();
    }
}