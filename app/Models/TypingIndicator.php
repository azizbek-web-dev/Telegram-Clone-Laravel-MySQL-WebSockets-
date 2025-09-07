<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypingIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'started_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
        ];
    }

    /**
     * Get the chat for the typing indicator.
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user who is typing.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}