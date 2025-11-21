<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
      protected $fillable = [
        'title',
        'slug',
        'content',
        'category_id',
        'user_id',
        'excerpt',
        'thumbnail',
        'status',
        'published_at'
    ];
}
