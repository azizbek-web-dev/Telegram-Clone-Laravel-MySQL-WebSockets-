<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the message that was read.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the user who read the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}