<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'reply_to_message_id',
        'forward_from_message_id',
        'message_type',
        'content',
        'file_url',
        'file_name',
        'file_size',
        'duration',
        'thumbnail_url',
        'is_edited',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    /**
     * Get the chat that owns the message.
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user who sent the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the message this is replying to.
     */
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    /**
     * Get the message this was forwarded from.
     */
    public function forwardFrom()
    {
        return $this->belongsTo(Message::class, 'forward_from_message_id');
    }

    /**
     * Get the message reads for this message.
     */
    public function messageReads()
    {
        return $this->hasMany(MessageRead::class);
    }
}