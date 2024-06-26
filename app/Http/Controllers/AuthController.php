<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request) {
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);


        if (User::where('email', $request->email)->exists()) { return response()->json(["Error" => "User exists"], 409); }
        
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        $token = $user->createToken("client");
        $user->token = $token->plainTextToken;

        return response()->json($user);
    }

    public function login(Request $request) {
        $request->validate([
            "email" => "required|email",
            "password" => "required|min:8"
        ]);
        
        $user = User::where('email', $request->email())->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) { return response()->json(["Error" => "Invalid credentials"], 401); }

        return response()->json($user);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response(null, 204);
    }
}
