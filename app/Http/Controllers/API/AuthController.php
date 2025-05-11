<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        
        // Mock login for testing - in a real app, you'd check against a database
        // Admin login
        if ($request->username === 'admin' && $request->password === 'admin123') {
            return response()->json([
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                    'name' => 'Admin User',
                ],
                'userType' => 'admin',
                'token' => 'test_token_for_admin' // In a real app, use JWT or Sanctum
            ]);
        }
        
        // Teacher login (just for testing)
        if (strpos($request->username, 'teacher') === 0 && $request->password === 'teacher123') {
            // Extract section from username (e.g., teachera → section A)
            $section = strtoupper(substr($request->username, -1));
            
            return response()->json([
                'user' => [
                    'id' => 2,
                    'username' => $request->username,
                    'name' => 'Teacher ' . $section,
                    'section' => $section,
                ],
                'userType' => 'teacher',
                'token' => 'test_token_for_teacher' 
            ]);
        }
        
        // Parent login (just for testing)
        if ($request->username === 'parent' && $request->password === 'parent123') {
            return response()->json([
                'user' => [
                    'id' => 3,
                    'username' => 'parent',
                    'name' => 'Parent User',
                ],
                'userType' => 'parent',
                'token' => 'test_token_for_parent'
            ]);
        }
        
        // Invalid credentials
        return response()->json([
            'message' => 'Invalid username or password'
        ], 401);
    }
}