<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
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

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'password' => 'sometimes|string|min:6',
            'picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if (empty($validated)) {
            return response()->json([
                'message' => 'No valid fields provided for update',
            ], 422);
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('users', $filename, 'public');
            $validated['picture'] = '/storage/' . $path;
        }

        $user->update($validated);

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
