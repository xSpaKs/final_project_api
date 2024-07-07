<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\Purchase;
use App\Mail\Suspension;
use App\Mail\Reactivation;

use Stripe\Product;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Price;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;


class StripeController extends Controller
{
    public function checkout(Request $request) {
        
        // Check data from request
        $request->validate([
            'product' => 'required|string',
            'subscription_type' => 'required|in:month,year',
            'discount' => 'sometimes|required',
        ]);

        Stripe::setApiKey(getenv("STRIPE_SECRET")); // Setup Stripe environment

        $product = Product::retrieve($request->product); // Get product from database

        $prices = Price::all(['product' => $product->id])->data; // Get associated prices

        $price = $request->subscription_type == "year" ? $prices[0] : $prices[1]; // Choose price depending on user's choice

        // Create a Stripe session
        $stripeCheckoutSession = Session::create([
          'line_items' => [[
            'price' => $price->id,
            'quantity' => 1,
          ]],
          'mode' => 'subscription',
          'allow_promotion_codes' => true,
          'metadata' => [
                'user_id' => $request->user()->id
            ],
          'success_url' => 'http://localhost:5173/checkout/success', 
          'cancel_url' => 'http://localhost:5173/checkout/cancel', 
        ]);

        return response()->json(['url' => $stripeCheckoutSession->url]);
    }

    // Get data from Stripe about what user is doing
    public function webhook() {
        Stripe::setApiKey(getenv("STRIPE_SECRET"));

        $stripe = new StripeClient(getenv("STRIPE_SECRET"));

        $endpoint_secret = getenv("STRIPE_WEBHOOK_SECRET");

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
          $event = Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
          );
          
        } catch(\UnexpectedValueException $e) {
          // Invalid payload
          http_response_code(400);
          exit();
        } catch(SignatureVerificationException $e) {
          // Invalid signature
          http_response_code(400);
          exit();
        }

        Log::debug($event);

        // If a payment has succeedeed, insert product's data in database
        if ($event->type == "invoice.payment_succeeded") {
            $subscriptionId = $event->data->object->lines->data[0]->price->product;
            $name = Product::retrieve($subscriptionId)->name;
            $price = $event->data->object->lines->data[0]->amount;
            $interval = $event->data->object->lines->data[0]->plan->interval;
            
            Payment::create([
                "user_id" => -1,
                "subscription_id" => $subscriptionId,
                "name" => $name,
                "price" => $price,
                "interval" => $interval,
            ]);
        }

        if ($event->type == "checkout.session.completed") {
            $userId = $event->data->object->metadata->user_id;
            $stripeCustomerId = $event->data->object->customer;
            
            Payment::where('user_id', -1)->update(['user_id' => $userId]); // Link user ID with the previously inserted product in database
            $user = User::where('id', $userId)->first();
            $user->update(['stripe_customer_id' => $stripeCustomerId]); // Add a Stripe customer ID to user in database

            $data = [
                'mail' => "planetary@gmail.com",
                'content' => "You have subscribed to a new planet ! Check out your user page for more informations."
            ];
    
            Mail::to($user->email)->send(new Purchase($data)); // Send purchase confirmation mail to user
        }

        // If user cancels a subscription, delete it from database and send an email to user
        if ($event->type == "customer.subscription.updated" && $event->data->object->cancel_at_period_end == true) {
            $subscriptionId = $event->data->object->items->data[0]->price->product;
            $userId = User::where("stripe_customer_id", $event->data->object->customer)->first()->id;
            
            $user = User::where('id', $userId)->first();
            Payment::where("user_id", $userId)->where("subscription_id", $subscriptionId)->delete();

            $data = [
                'mail' => "planetary@gmail.com",
                'content' => "You have suspended one of your subscription :( \nBut don't worry, you can reactive it at any moment on your user page."
            ];
    
            Mail::to($user->email)->send(new Suspension($data));
        }

        // If user uncancels a subscription, insert it in database and send an email to user
        if ($event->type == "customer.subscription.updated" && $event->data->object->cancel_at_period_end == false) {
            
            $userId = User::where("stripe_customer_id", $event->data->object->customer)->first()->id;
            $subscriptionId = $event->data->object->items->data[0]->price->product;
            $name = Product::retrieve($subscriptionId)->name;
            $price = $event->data->object->items->data[0]->price->unit_amount;
            $interval = $event->data->object->items->data[0]->plan->interval;

            $user = User::where('id', $userId)->first();
            Payment::create([
                "user_id" => $userId,
                "subscription_id" => $subscriptionId,
                "name" => $name,
                "price" => $price,
                "interval" => $interval,
            ]);

            $data = [
                'mail' => "planetary@gmail.com",
                'content' => "You have reactivated one of your subscription :) \nCheck out your user page for more informations."
            ];
    
            Mail::to($user->email)->send(new Reactivation($data));
        }

        http_response_code(200);
    }

    public function customer(Request $request) {
        $stripe = new StripeClient(getenv("STRIPE_SECRET"));

        $stripeCustomerId = $request->user()->stripe_customer_id;

        if (!$stripeCustomerId) {
            return response()->json(['error' => "User does not have a subscription"], 400);
        }

        $customerPortal = $stripe->billingPortal->sessions->create([
            'customer' => $stripeCustomerId,
            'return_url' => 'http://localhost:5173/user',
        ]);

        return response()->json(['url' => $customerPortal->url]);
    }

    public function subscriptions(Request $request)
    {
        try {
            Stripe::setApiKey(getenv("STRIPE_SECRET"));

            $products = Product::all();

            $formattedProducts = [];

            foreach ($products->data as $product) {

                $prices = Price::all(['product' => $product->id])->data;
                
                $price1 = $prices[0];
                $price2 = $prices[1];
        
                $price1InEuros = number_format($price1->unit_amount / 100, 2);
                $price2InEuros = number_format($price2->unit_amount / 100, 2);
        
                $formattedProduct = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price1' => [
                        'amount' => $price1InEuros,
                        'interval' => $price1->recurring->interval
                    ],
                    'price2' => [
                        'amount' => $price2InEuros,
                        'interval' => $price2->recurring->interval
                    ],
                    'image' => $product->images[0],
                ];
        
                array_push($formattedProducts, $formattedProduct);
            }

            return response()->json($formattedProducts);

        } catch (\Exception $error) {
            return response()->json(['error' => $error->getMessage()], 500);
        }
    }

    public function subscription($id) {
        try {
            Stripe::setApiKey(getenv("STRIPE_SECRET"));
            
            $product = Product::retrieve($id);

            $prices = Price::all(['product' => $product->id])->data;
                
            $price1 = $prices[0];
            $price2 = $prices[1];
    
            $price1InEuros = number_format($price1->unit_amount / 100, 2);
            $price2InEuros = number_format($price2->unit_amount / 100, 2);
    
            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'price1' => [
                    'amount' => $price1InEuros,
                    'interval' => $price1->recurring->interval
                ],
                'price2' => [
                    'amount' => $price2InEuros,
                    'interval' => $price2->recurring->interval
                ],
                'image' => $product->images[0],
            ];

            return response()->json($formattedProduct);
        } catch (error) {
            return response()->json(["error" => "Product not found"], 404);
        }
    }
}
