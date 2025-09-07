<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'description',
        'image_url',
        'created_by',
        'is_public',
        'invite_link',
        'max_members',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the user who created the chat.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the members of the chat.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'chat_members')
                    ->withPivot('role', 'joined_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get the messages in the chat.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the typing indicators for the chat.
     */
    public function typingIndicators()
    {
        return $this->hasMany(TypingIndicator::class);
    }
}