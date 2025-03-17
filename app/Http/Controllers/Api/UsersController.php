<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * Find All User
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page');

        if (!$perPage) {
            $users = User::all();
        } else {
            $users = User::paginate((int) $perPage);
        }

        return response()->json($users);
    }

    /**
     * Create User
     * 
     * @requestMediaType multipart/form-data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if (request()->hasFile('picture')) {
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('posts', $filename, 'public');
            $data['picture'] = '/storage/' . $path;
        }

        $user = User::create($validated);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    /**
     * Find User by ID
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Edit User
     * @requestMediaType multipart/form-data
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'sometimes|string|min:6',
            'picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if (request()->hasFile('picture')) {
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('posts', $filename, 'public');
            $data['picture'] = '/storage/' . $path;
        }

        $user->update($validated);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    /**
     * Delete User
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
