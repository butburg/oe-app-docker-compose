<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // which fields can be mass-assigned
    protected $fillable = [
        'title',
        'image_file',
        'username',
        'is_published',
        'is_sensitive',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Laravel will handle converting these values to 1 and 0 when saving to the database 
    // and back to true and false when retrieving from the database.
    // protected $casts = [
    //     'is_published' => 'boolean',
    //     'is_sensitive' => 'boolean',
    // ];
}
