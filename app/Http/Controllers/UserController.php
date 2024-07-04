<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function user($id)
    {
        return User::where("id", $id)->first();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function modifyUser(Request $request)
    {
        if ($request->name == null && $request->email == null && $request->password == null) { return response()->json(['Error' => "Empty credentials"], 422);
    }

        $request->validate([
            "name" => "string|nullable",
            "email" => "email|nullable",
            "password" => "min:8|nullable",
        ]);

        $user = User::find($request->id);

        if (!$user) { return response()->json(['error' => "User does not exist"], 404); }

        if (!empty($request->name)) { $user->name = $request->name; }
        if (!empty($request->email)) { $user->email = $request->email; }
        if (!empty($request->password)) { $user->password = Hash::make($request->password); }

        $user->save();

        $user->tokens()->where("name", "client")->delete();
        $token = $user->createToken("client");

        $user->token = $token->plainTextToken;

        return response()->json($user, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteAccount(Request $request)
    {
        User::where("id", $request->user()->id)->delete();

        return response()->json(["message" => "User successfully deleted"], 200);
    }
}
