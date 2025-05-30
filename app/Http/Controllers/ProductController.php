<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Transaction;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return view('products.show', [
            'product' => $product,
            'stripeKey' => config('cashier.key'),
        ]);
    }

    public function charge(Request $request, Product $product)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        Stripe::setApiKey(config('cashier.secret'));

        // Create a new customer
        $customer = \Stripe\Customer::create([
            'payment_method' => $request->payment_method,
            'invoice_settings' => [
                'default_payment_method' => $request->payment_method,
            ],
            'email' => $request->input('email'),
        ]);

        // Create a payment intent
        $paymentIntent = PaymentIntent::create([
            'amount' => $product->price * 100, // cents
            'currency' => 'usd',
            'customer' => $customer->id,
            'payment_method' => $request->payment_method,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
            'description' => "Purchase of {$product->name}",
        ]);

        // Store transaction
        Transaction::create([
            'product_id' => $product->id,
            'stripe_payment_intent_id' => $paymentIntent->id,
            'amount' => $product->price,
            'status' => $paymentIntent->status,
            'email' => $request->input('email') ?? null,
        ]);

        return redirect()->route('products.index')->with('success', 'Thank you! Your payment was successful.');
    }
}
