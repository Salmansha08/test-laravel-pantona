<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show'])
        ];
    }

    /**
     * Find All User
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page');

        if (!$perPage) {
            $users = User::orderBy('created_at', 'desc')->get();
        } else {
            $users = User::orderBy('created_at', 'desc')->paginate((int) $perPage);
        }

        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ], 200);
    }

    /**
     * Create User
     * 
     * @requestMediaType multipart/form-data
     */
    public function store(Request $request): JsonResponse
    {
        $filelds = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $filelds['password'] = Hash::make($filelds['password']);

        $filelds = request()->only(['name', 'email', 'password']);

        if (request()->hasFile('picture')) {
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('users', $filename, 'public');
            $filelds['picture'] = '/storage/' . $path;
        }

        $user = User::create($filelds);

        return response()->json([
            'message' => 'User has been registered successfully.',
            'data' => $user
        ], 201);
    }

    /**
     * Find User by ID
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => $user,
        ], 200);
    }

    /**
     * Edit User
     * @requestMediaType multipart/form-data
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $oldPicture = $user->picture;

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $updateData = request()->only(['name', 'email']);

        if (request()->hasFile('picture')) {
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('users', $filename, 'public');
            $updateData['picture'] = '/storage/' . $path;
        }

        $user->update($updateData);
        $user = $user->fresh();

        if ($request->hasFile('picture') && $oldPicture && ($oldPicture !== $user->picture)) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $oldPicture));
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user,
        ], 200);
    }

    /**
     * Delete User
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}
