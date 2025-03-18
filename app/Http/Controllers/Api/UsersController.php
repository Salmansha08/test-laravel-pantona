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
            $users = User::orderBy('created_at', 'desc')->get();
        } else {
            $users = User::orderBy('created_at', 'desc')->paginate((int) $perPage);
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
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $data['password'] = Hash::make($data['password']);

        $data = request()->only(['name', 'email', 'password']);

        if (request()->hasFile('picture')) {
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('users', $filename, 'public');
            $data['picture'] = '/storage/' . $path;
        }

        $user = User::create($data);

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

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'sometimes|string|min:6',
            'picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if (isset($request['password'])) {
            $request['password'] = Hash::make($request['password']);
        }

        $data = request()->only(['name', 'email', 'password']);

        if (request()->hasFile('picture')) {
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('users', $filename, 'public');
            $data['picture'] = '/storage/' . $path;
        }

        $user->update($data);

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
