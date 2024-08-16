<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageVariant extends Model
{
    use HasFactory;

    // Define the fillable attributes for mass assignment
    protected $fillable = ['image_id', 'path', 'size_type', 'quality', 'width', 'height'];

    // Define the relationship with Image
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
