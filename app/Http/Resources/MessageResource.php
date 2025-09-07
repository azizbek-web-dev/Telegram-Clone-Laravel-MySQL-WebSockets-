<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'message_type' => $this->message_type,
            'content' => $this->content,
            'file_url' => $this->file_url,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'duration' => $this->duration,
            'thumbnail_url' => $this->thumbnail_url,
            'is_edited' => $this->is_edited,
            'edited_at' => $this->edited_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'sender' => $this->whenLoaded('sender', function () {
                return [
                    'id' => $this->sender->id,
                    'username' => $this->sender->username,
                    'full_name' => $this->sender->full_name,
                    'profile_image_url' => $this->sender->profile_image_url,
                ];
            }),
            'reply_to' => $this->whenLoaded('replyTo', function () {
                return [
                    'id' => $this->replyTo->id,
                    'content' => $this->replyTo->content,
                    'message_type' => $this->replyTo->message_type,
                    'created_at' => $this->replyTo->created_at?->toISOString(),
                    'sender' => [
                        'id' => $this->replyTo->sender->id,
                        'username' => $this->replyTo->sender->username,
                        'full_name' => $this->replyTo->sender->full_name,
                    ],
                ];
            }),
            'forward_from' => $this->whenLoaded('forwardFrom', function () {
                return [
                    'id' => $this->forwardFrom->id,
                    'content' => $this->forwardFrom->content,
                    'message_type' => $this->forwardFrom->message_type,
                    'created_at' => $this->forwardFrom->created_at?->toISOString(),
                    'sender' => [
                        'id' => $this->forwardFrom->sender->id,
                        'username' => $this->forwardFrom->sender->username,
                        'full_name' => $this->forwardFrom->sender->full_name,
                    ],
                ];
            }),
            'message_reads_count' => $this->when(isset($this->message_reads_count), $this->message_reads_count),
            'message_reads' => $this->whenLoaded('messageReads', function () {
                return $this->messageReads->map(function ($read) {
                    return [
                        'id' => $read->id,
                        'user_id' => $read->user_id,
                        'read_at' => $read->read_at?->toISOString(),
                    ];
                });
            }),
        ];
    }
}