<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use HasFactory;
    // which fields can be mass-assigned
    protected $fillable = [
        'user_id',
        'title',
        'username',
        'is_published',
        'is_sensitive',
        'published_at',
    ];

    // using $casts for date casting
    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Define the relationship with User
    public function user() {
        return $this->belongsTo(User::class);
    }
    // Define the relationship with Image
    public function image() {
        return $this->hasOne(Image::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    // Laravel will handle converting these values to 1 and 0 when saving to the database 
    // and back to true and false when retrieving from the database.
    // protected $casts = [
    //     'is_published' => 'boolean',
    //     'is_sensitive' => 'boolean',
    // ];
}
