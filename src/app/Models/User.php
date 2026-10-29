<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Mail\ConfirmNewEmail;
use App\Models\Session;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;


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

    /**
     * Mail notifications the user can switch off in the profile, all on by default.
     * Announcements from the admins (FEAT-017) are not part of it, they cannot be switched off.
     */
    public const NOTIFICATION_SETTINGS = [
        'notify_comments' => [
            'label' => 'Comments on my images',
            'description' => 'A mail when someone comments on one of your images.',
        ],
        'notify_new_images' => [
            'label' => 'New images',
            'description' => 'A mail when other users publish new images, several images of one person in one mail.',
        ],
    ];

    /**
     * How long the link for a new email from the profile stays valid.
     */
    public const EMAIL_CHANGE_VALID_MINUTES = 60;

    /**
     * Mail a link to the new email. The current email stays active until the link is opened,
     * so a mistyped address never locks the user out.
     */
    public function requestEmailChange(string $email): void {
        $this->pending_email = $email;
        $this->save();

        $this->sendEmailChangeLink();
    }

    public function sendEmailChangeLink(): void {
        $url = URL::temporarySignedRoute(
            'profile.email.confirm',
            now()->addMinutes(self::EMAIL_CHANGE_VALID_MINUTES),
            // The hash ties the link to this pending email, a newer request makes it invalid
            ['user' => $this->id, 'hash' => sha1($this->pending_email)]
        );

        Mail::to($this->pending_email)->send(new ConfirmNewEmail($this->name, $url, self::EMAIL_CHANGE_VALID_MINUTES));
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
            'notify_comments' => 'boolean',
            'notify_new_images' => 'boolean',
        ];
    }
}
