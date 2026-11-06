<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // API Status Check
    public function index()
    {
        return response()->json([
            'message' => 'API is running'
        ], 200);
    }

    // Register User (Create)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email',
            'mobile' => 'required|string|max:20',
            'address' => 'required|string|max:1000',
            'gender' => 'required|in:male,female,other',
            'institution_name' => 'required|string|max:255',
            'class_name' => 'required|string|max:100',

            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Profile Image Upload
        $imagePath = null;

        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')
                ->store('profile_images', 'public');
        }

        // Create User
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'address' => $validated['address'],
            'gender' => $validated['gender'],
            'institution_name' => $validated['institution_name'],
            'class_name' => $validated['class_name'],
            'profile_image' => $imagePath,
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'profile_image_url' => $imagePath
                ? Storage::url($imagePath)
                : null
        ], 201);
    }


// Login User with Email or Mobile
public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $loginInput = $request->username;

    // Search by Email or Mobile
    $user = User::where('email', $loginInput)
        ->orWhere('mobile', $loginInput)
        ->first();

    // Check Credentials
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    // Generate Sanctum Token
    $token = $user->createToken('auth_token')->plainTextToken;

    // Profile Image URL
    $profileImageUrl = $user->profile_image
        ? asset('storage/' . $user->profile_image)
        : null;

    return response()->json([
        'message' => 'Login successful',

        'user' => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'profile_image' => $user->profile_image,
        ],

        'token' => $token,
        'profile_image_url' => $profileImageUrl

    ], 200);
}


    // Read All Users
    public function getAllUsers()
    {
        $users = User::all();

        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users
        ], 200);
    }


    // Read Single User by ID
    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => $user,
            'profile_image_url' => $user->profile_image
                ? Storage::url($user->profile_image)
                : null
        ], 200);
    }


    // Update User
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',

            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'mobile' => 'sometimes|required|string|max:20',
            'address' => 'sometimes|required|string|max:1000',
            'gender' => 'sometimes|required|in:male,female,other',
            'institution_name' => 'sometimes|required|string|max:255',
            'class_name' => 'sometimes|required|string|max:100',

            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Update Profile Image
        if ($request->hasFile('profile_image')) {

            // Delete Previous Image
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Upload New Image
            $validated['profile_image'] = $request->file('profile_image')
                ->store('profile_images', 'public');
        }

        // Update User Information
        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user,
            'profile_image_url' => $user->profile_image
                ? Storage::url($user->profile_image)
                : null
        ], 200);
    }


    // Delete User
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Delete Profile Image
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Delete User
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}
