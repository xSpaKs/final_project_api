<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactOwner;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmailContactOwner(Request $request)
    {
        $data = [
            'name' => $request->name,
            'mail' => $request->email,
            'object' => $request->object,
            'content' => $request->message,
        ];

        Mail::to(env('MAIL_OWNER'))->send(new ContactOwner($data));

        return response()->json(['message' => 'Email sent successfully!']);
    }

    public function sendEmailPurchase(Request $request)
    {
        

        Mail::to(env('MAIL_OWNER'))->send(new PurchaseOwner($data));
        Mail::to(env('mail.client@gmail.com'))->send(new PurchaseOwner($data));

        return response()->json(['message' => 'Email sent successfully!']);
    }

    public function sendEmailPurchaseOwner()
    {
        $data = [
            'mail' => 'espace-client@gmail.com',
            'content' => 'Salut mon pote',
        ];
        
        Mail::to(env('MAIL_OWNER'))->send(new PurchaseOwner($data));
    }
}
