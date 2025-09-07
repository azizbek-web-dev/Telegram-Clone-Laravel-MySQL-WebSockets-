<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $chats = $user->chats()
            ->with(['members' => function($query) {
                $query->select('users.id', 'username', 'full_name', 'profile_image_url')
                      ->wherePivot('is_active', true);
            }])
            ->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['chats' => $chats]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:private,group,channel',
            'name' => 'required_if:type,group,channel|string|max:255',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'required_if:type,private|array|min:1|max:1',
            'user_ids' => 'required_if:type,group|array|min:1|max:200',
            'user_ids.*' => 'exists:users,id',
            'is_public' => 'boolean',
            'max_members' => 'integer|min:2|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $chat = Chat::create([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $user->id,
            'is_public' => $request->boolean('is_public', false),
            'max_members' => $request->get('max_members', 200),
        ]);

        $chat->members()->attach($user->id, [
            'role' => 'owner',
            'joined_at' => Carbon::now(),
            'is_active' => true,
        ]);

        if ($request->has('user_ids')) {
            foreach ($request->user_ids as $userId) {
                if ($userId != $user->id) {
                    $chat->members()->attach($userId, [
                        'role' => 'member',
                        'joined_at' => Carbon::now(),
                        'is_active' => true,
                    ]);
                }
            }
        }

        if ($chat->is_public) {
            $chat->update(['invite_link' => Str::random(32)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chat created successfully',
            'data' => ['chat' => $chat->load('members')]
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })
        ->with(['members' => function($query) {
            $query->select('users.id', 'username', 'full_name', 'profile_image_url')
                  ->wherePivot('is_active', true);
        }])
        ->withCount('messages')
        ->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['chat' => $chat]
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('role', ['owner', 'admin'])
                  ->where('is_active', true);
        })->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or insufficient permissions'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
            'max_members' => 'integer|min:2|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $chat->update($request->only(['name', 'description', 'is_public', 'max_members']));

        return response()->json([
            'success' => true,
            'message' => 'Chat updated successfully',
            'data' => ['chat' => $chat->fresh()]
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('role', 'owner')
                  ->where('is_active', true);
        })->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or insufficient permissions'
            ], 404);
        }

        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat deleted successfully'
        ]);
    }

    public function addMember(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('role', ['owner', 'admin'])
                  ->where('is_active', true);
        })->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or insufficient permissions'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:admin,member',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($chat->members()->where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this chat'
            ], 400);
        }

        if ($chat->members()->count() >= $chat->max_members) {
            return response()->json([
                'success' => false,
                'message' => 'Chat has reached maximum member limit'
            ], 400);
        }

        $chat->members()->attach($request->user_id, [
            'role' => $request->get('role', 'member'),
            'joined_at' => Carbon::now(),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully'
        ]);
    }

    public function removeMember(Request $request, $id, $userId): JsonResponse
    {
        $user = $request->user();
        
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('role', ['owner', 'admin'])
                  ->where('is_active', true);
        })->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or insufficient permissions'
            ], 404);
        }

        $memberToRemove = $chat->members()->where('user_id', $userId)->first();
        if ($memberToRemove && $memberToRemove->pivot->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove chat owner'
            ], 400);
        }

        $chat->members()->updateExistingPivot($userId, ['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully'
        ]);
    }

    public function updateMemberRole(Request $request, $id, $userId): JsonResponse
    {
        $user = $request->user();
        
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('role', 'owner')
                  ->where('is_active', true);
        })->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or insufficient permissions'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,member',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $chat->members()->updateExistingPivot($userId, ['role' => $request->role]);

        return response()->json([
            'success' => true,
            'message' => 'Member role updated successfully'
        ]);
    }

    public function generateInviteLink(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $chat = Chat::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('role', ['owner', 'admin'])
                  ->where('is_active', true);
        })->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found or insufficient permissions'
            ], 404);
        }

        if (!$chat->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Only public chats can have invite links'
            ], 400);
        }

        $inviteLink = Str::random(32);
        $chat->update(['invite_link' => $inviteLink]);

        return response()->json([
            'success' => true,
            'data' => ['invite_link' => $inviteLink]
        ]);
    }

    public function joinByLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invite_link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $chat = Chat::where('invite_link', $request->invite_link)
            ->where('is_public', true)
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invite link'
            ], 404);
        }

        $user = $request->user();

        if ($chat->members()->where('user_id', $user->id)->where('is_active', true)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this chat'
            ], 400);
        }

        if ($chat->members()->count() >= $chat->max_members) {
            return response()->json([
                'success' => false,
                'message' => 'Chat has reached maximum member limit'
            ], 400);
        }

        $chat->members()->attach($user->id, [
            'role' => 'member',
            'joined_at' => Carbon::now(),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined chat',
            'data' => ['chat' => $chat->load('members')]
        ]);
    }
}