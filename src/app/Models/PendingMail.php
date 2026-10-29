<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One item waiting for the next mail bundle of a user (FEAT-020).
 */
class PendingMail extends Model
{
    public const COMMENT = 'comment';
    public const NEW_IMAGE = 'new_image';
    public const ANNOUNCEMENT = 'announcement';

    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'type', 'comment_id', 'post_id', 'admin_mail_id', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function adminMail()
    {
        return $this->belongsTo(AdminMail::class);
    }
}
