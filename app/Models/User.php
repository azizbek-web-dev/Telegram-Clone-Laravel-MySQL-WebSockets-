<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'full_name',
        'bio',
        'profile_image_url',
        'phone',
        'date_of_birth',
        'is_verified',
        'is_active',
        'last_seen',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'date_of_birth' => 'date',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'last_seen' => 'datetime',
        ];
    }

    /**
     * Get the email verifications for the user.
     */
    public function emailVerifications()
    {
        return $this->hasMany(EmailVerification::class);
    }

    /**
     * Get the user sessions for the user.
     */
    public function userSessions()
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Get the profile images for the user.
     */
    public function profileImages()
    {
        return $this->hasMany(UserProfileImage::class);
    }

    /**
     * Get the chats where the user is a member.
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_members')
                    ->withPivot('role', 'joined_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get the messages sent by the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the message reads by the user.
     */
    public function messageReads()
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Get the typing indicators for the user.
     */
    public function typingIndicators()
    {
        return $this->hasMany(TypingIndicator::class);
    }
}
