<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['profileImages', 'userSessions' => function($query) {
            $query->where('is_active', true)->latest();
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->makeHidden(['password_hash'])
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'full_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'username', 'full_name', 'bio', 'phone', 'date_of_birth'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user->fresh()->makeHidden(['password_hash'])
            ]
        ]);
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Store image
        $path = $request->file('image')->store('profile-images', 'public');
        $imageUrl = Storage::url($path);

        // If this is set as primary, unset other primary images
        if ($request->boolean('is_primary')) {
            $user->profileImages()->update(['is_primary' => false]);
        }

        // Create profile image record
        $profileImage = $user->profileImages()->create([
            'image_url' => $imageUrl,
            'is_primary' => $request->boolean('is_primary', false),
            'uploaded_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile image uploaded successfully',
            'data' => [
                'profile_image' => $profileImage
            ]
        ]);
    }

    /**
     * Delete profile image
     */
    public function deleteProfileImage(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $profileImage = $user->profileImages()->find($id);

        if (!$profileImage) {
            return response()->json([
                'success' => false,
                'message' => 'Profile image not found'
            ], 404);
        }

        // Delete file from storage
        $filePath = str_replace('/storage/', '', $profileImage->image_url);
        Storage::disk('public')->delete($filePath);

        // Delete database record
        $profileImage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile image deleted successfully'
        ]);
    }

    /**
     * Search users
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'limit' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->get('query');
        $limit = $request->get('limit', 20);

        $users = User::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('username', 'like', "%{$query}%")
                  ->orWhere('full_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select(['id', 'username', 'full_name', 'profile_image_url', 'is_verified'])
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }

    /**
     * Get user sessions
     */
    public function getSessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $sessions = $user->userSessions()
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sessions' => $sessions
            ]
        ]);
    }

    /**
     * Delete user session
     */
    public function deleteSession(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $session = $user->userSessions()->find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        }

        $session->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Session deleted successfully'
        ]);
    }
}