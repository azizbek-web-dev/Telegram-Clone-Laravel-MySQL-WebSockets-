<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'is_public' => $this->is_public,
            'invite_link' => $this->invite_link,
            'max_members' => $this->max_members,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'username' => $this->creator->username,
                    'full_name' => $this->creator->full_name,
                    'profile_image_url' => $this->creator->profile_image_url,
                ];
            }),
            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'username' => $member->username,
                        'full_name' => $member->full_name,
                        'profile_image_url' => $member->profile_image_url,
                        'role' => $member->pivot->role,
                        'joined_at' => $member->pivot->joined_at?->toISOString(),
                        'is_active' => $member->pivot->is_active,
                    ];
                });
            }),
            'messages_count' => $this->when(isset($this->messages_count), $this->messages_count),
            'last_message' => $this->whenLoaded('messages', function () {
                $lastMessage = $this->messages->first();
                if (!$lastMessage) return null;
                
                return [
                    'id' => $lastMessage->id,
                    'content' => $lastMessage->content,
                    'message_type' => $lastMessage->message_type,
                    'created_at' => $lastMessage->created_at?->toISOString(),
                    'sender' => [
                        'id' => $lastMessage->sender->id,
                        'username' => $lastMessage->sender->username,
                        'full_name' => $lastMessage->sender->full_name,
                    ],
                ];
            }),
        ];
    }
}