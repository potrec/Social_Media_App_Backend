<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Post extends Eloquent
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'posts';
    protected $fillable = ['user_id', 'parent_id', 'body'];
    
    /**
     * @var array
     */
    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
