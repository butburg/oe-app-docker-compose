<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    // Define the fillable attributes for mass assignment
    protected $fillable = ['original_path', 'post_id'];
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
}
