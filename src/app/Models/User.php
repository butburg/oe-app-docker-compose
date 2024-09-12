<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Session;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements MustVerifyEmail {
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'profile_image', 'usertype', 'description'];

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function image() {
        return $this->hasOne(Image::class);
    }

    // Define a custom hasOne relationship to the sessions table
    public function session() {
        return $this->hasOne(Session::class, 'user_id', 'id');
    }

    public function getPostsCountAttribute(): int {
        return $this->posts()->count();
    }

    /**
     * Get the former name if it is within the given days limit.
     *
     * @param int $daysLimit
     * @return string|null
     */
    public function getFormerNameIfApplicable(int $daysLimit = 90): ?string {
        if ($this->previous_name) {
            $lastNameChangeDate = $this->last_name_change ? Carbon::parse($this->last_name_change) : null;
            $daysPassed = $lastNameChangeDate ? $lastNameChangeDate->diffInDays(Carbon::now()) : null;

            if ($daysPassed !== null && $daysPassed <= $daysLimit) {
                return $this->previous_name;
            }
        }

        return null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
