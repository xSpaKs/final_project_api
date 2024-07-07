<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\EmailController;

class AuthController extends Controller
{
    public function register(Request $request) {

        // Check data from request
        $request->validate([
            "name" => "required",
            "email" => "required|email",
            "password" => "required|min:8",
        ]);

        // Return an error if user does not exist
        if (User::where('email', $request->email)->exists()) { return response()->json(['error' => "User exists"], 409); }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Send registration confirmation to user
        $emailController = new EmailController();
        $emailController->sendEmailRegister($request->email);

        // Create authentification token for user
        $token = $user->createToken("client");
        $user->token = $token->plainTextToken;

        return response()->json($user);
    }

    public function login(Request $request) {

        // Check data from request
        $request->validate([
            "email" => "required|email",
            "password" => "required|min:8",
        ]);

        $user = User::where('email', $request->email)->first();

        // Return an error if credentials do not match database
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => "Invalid credentials"], 401);
        }

        // Create a new authentification token for user
        $user->tokens()->where("name", "client")->delete();
        $token = $user->createToken("client");
        $user->token = $token->plainTextToken;

        return response()->json($user);
    }

    public function logout(Request $request) {
        
        // Delete authentification token of user
        $request->user()->currentAccessToken()->delete();
        return response(null, 204);
    }
}
