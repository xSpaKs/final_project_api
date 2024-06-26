<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        \Stripe/Stripe::setApiKey(getenv("STRIPE_SECRET"));

        $stripeCheckoutSession = \Stripe\Checkout\Session::create([
            'line_items' => [[
                'price' => 'price_1P...',
                'quantity' => '1',
            ]],
            'mode' => 'subscription',
            'success_url' => 'http://127.0.0.1:8001/success',
            'cancel_url' => 'http://127.0.0.1:8001/cancel',
            ]);

        return response()->json(["url" => $stripeCheckoutSession->url]);
    }
}
