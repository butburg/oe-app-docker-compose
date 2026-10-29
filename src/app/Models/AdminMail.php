<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMail extends Model
{
    protected $fillable = ['user_id', 'subject', 'body', 'group', 'recipient_email', 'recipient_count', 'send_token'];

    // The admin who sent it
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
