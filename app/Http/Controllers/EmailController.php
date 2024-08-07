<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactOwner;
use App\Mail\Register;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmailContactOwner(Request $request)
    {
        // Check data from request
        $request->validate([
            "name" => "required",
            "email" => "required|email",
            "object" => "required",
            "message" => "required|min:50",
        ]);

        $data = [
            'name' => $request->name,
            'mail' => $request->email,
            'object' => $request->object,
            'content' => $request->message,
        ];

        // Send mail with request's data
        Mail::to(env('MAIL_OWNER'))->send(new ContactOwner($data));

        return response()->json(['message' => 'Email sent successfully!']);
    }

    public function sendEmailRegister($mail)
    {
        $data = [
            'mail' => "planetary@gmail.com",
            'object' => "Bienvenue sur Planetary",
            'content' => "Bienvenue sur Planetary, votre compte a bien été enregistré !",
        ];

        Mail::to($mail)->send(new Register($data));

        return response()->json(['message' => 'Email sent successfully!']);
    }
}
