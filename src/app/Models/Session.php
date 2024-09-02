<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    
    protected $table = 'sessions';

    // Define the inverse relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
