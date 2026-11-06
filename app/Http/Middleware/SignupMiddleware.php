<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SignupMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validator = Validator::make($request->all(), [

            // Personal Information
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',

            // Contact Information
            'email' => 'required|string|email|max:255',
            'mobile' => 'required|string|max:20',
            'address' => 'required|string|max:1000',

            // Additional Information
            'gender' => 'required|in:male,female,other',
            'institution_name' => 'required|string|max:255',
            'class_name' => 'required|string|max:100',

            // Profile Image
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Password
            'password' => 'required|string|min:8|confirmed',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Signup validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        return $next($request);
    }
}
