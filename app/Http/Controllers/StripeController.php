<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Price;

class StripeController extends Controller
{
    public function checkout(Request $request) {
        $request->validate([
            'product' => 'required|string'
        ]);


        Stripe::setApiKey(getenv("STRIPE_SECRET"));

        $product = Product::retrieve($request->product);
        
        return response()->json($product);

        $stripeCheckoutSession = \Stripe\Checkout\Session::create([
          'line_items' => [[
            'price' => $product->stripe_price_id,
            'quantity' => 1,
          ]],
          'mode' => 'subscription',
          'allow_promotion_codes' => true,
          'success_url' => route('stripe.success'),
          'cancel_url' => route('stripe.cancel')
        ]);

        return redirect($stripeCheckoutSession->url);
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
        
        Stripe::setApiKey(getenv("STRIPE_SECRET"));
    
        try {
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
