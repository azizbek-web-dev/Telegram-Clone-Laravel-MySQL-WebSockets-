<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Chat;
use App\Models\MessageRead;
use App\Models\TypingIndicator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Get messages for a chat
     */
    public function getChatMessages(Request $request, $chatId): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is member of the chat
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->find($chatId);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or access denied'
            ], 404);
        }

        $perPage = $request->get('per_page', 20);
        $messages = Message::where('chat_id', $chatId)
            ->with(['sender:id,username,full_name,profile_image_url', 'replyTo', 'forwardFrom'])
            ->withCount('messageReads')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages
            ]
        ]);
    }

    /**
     * Send a message
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id',
            'message_type' => 'required|in:text,image,video,audio,file,voice,location',
            'content' => 'required_if:message_type,text|string|max:4000',
            'file_url' => 'required_if:message_type,image,video,audio,file,voice|string',
            'file_name' => 'nullable|string|max:255',
            'file_size' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:0',
            'thumbnail_url' => 'nullable|string',
            'reply_to_message_id' => 'nullable|exists:messages,id',
            'forward_from_message_id' => 'nullable|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Check if user is member of the chat
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->find($request->chat_id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or access denied'
            ], 404);
        }

        $message = Message::create([
            'chat_id' => $request->chat_id,
            'sender_id' => $user->id,
            'reply_to_message_id' => $request->reply_to_message_id,
            'forward_from_message_id' => $request->forward_from_message_id,
            'message_type' => $request->message_type,
            'content' => $request->content,
            'file_url' => $request->file_url,
            'file_name' => $request->file_name,
            'file_size' => $request->file_size,
            'duration' => $request->duration,
            'thumbnail_url' => $request->thumbnail_url,
        ]);

        // Update chat's updated_at timestamp
        $chat->touch();

        $message->load(['sender:id,username,full_name,profile_image_url', 'replyTo', 'forwardFrom']);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'message' => $message
            ]
        ], 201);
    }

    /**
     * Get specific message
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $message = Message::whereHas('chat.members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })
        ->with(['sender:id,username,full_name,profile_image_url', 'replyTo', 'forwardFrom'])
        ->withCount('messageReads')
        ->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $message
            ]
        ]);
    }

    /**
     * Update message
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $message = Message::where('sender_id', $user->id)->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found or insufficient permissions'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:4000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $message->update([
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => [
                'message' => $message->fresh()
            ]
        ]);
    }

    /**
     * Delete message
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $message = Message::where('sender_id', $user->id)->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found or insufficient permissions'
            ], 404);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $message = Message::whereHas('chat.members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        // Check if already read
        $existingRead = MessageRead::where('message_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$existingRead) {
            MessageRead::create([
                'message_id' => $id,
                'user_id' => $user->id,
                'read_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Forward message
     */
    public function forward(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $originalMessage = Message::whereHas('chat.members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->find($id);

        if (!$originalMessage) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is member of target chat
        $targetChat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->find($request->chat_id);

        if (!$targetChat) {
            return response()->json([
                'success' => false,
                'message' => 'Target chat not found or access denied'
            ], 404);
        }

        $forwardedMessage = Message::create([
            'chat_id' => $request->chat_id,
            'sender_id' => $user->id,
            'forward_from_message_id' => $id,
            'message_type' => $originalMessage->message_type,
            'content' => $originalMessage->content,
            'file_url' => $originalMessage->file_url,
            'file_name' => $originalMessage->file_name,
            'file_size' => $originalMessage->file_size,
            'duration' => $originalMessage->duration,
            'thumbnail_url' => $originalMessage->thumbnail_url,
        ]);

        $targetChat->touch();

        return response()->json([
            'success' => true,
            'message' => 'Message forwarded successfully',
            'data' => [
                'message' => $forwardedMessage->load(['sender:id,username,full_name,profile_image_url'])
            ]
        ], 201);
    }

    /**
     * Start typing indicator
     */
    public function startTyping(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Check if user is member of the chat
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->find($request->chat_id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or access denied'
            ], 404);
        }

        // Update or create typing indicator
        TypingIndicator::updateOrCreate(
            [
                'chat_id' => $request->chat_id,
                'user_id' => $user->id,
            ],
            [
                'started_at' => Carbon::now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Typing indicator started'
        ]);
    }

    /**
     * Stop typing indicator
     */
    public function stopTyping(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        TypingIndicator::where('chat_id', $request->chat_id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Typing indicator stopped'
        ]);
    }
}