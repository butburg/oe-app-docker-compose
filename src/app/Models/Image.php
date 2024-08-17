<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    // Define the fillable attributes for mass assignment
    protected $fillable = ['upload_size', 'post_id', 'user_id'];
    // Define the relationship with ImageVariant
    public function variants()
    {
        return $this->hasMany(ImageVariant::class);
    }
    // Define the relationship with Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    // Define the relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
