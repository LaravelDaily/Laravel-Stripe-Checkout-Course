<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $products = Product::all();

        return view('home', compact('products'));
    }

    public function buy($product_id)
    {
        $product = Product::findOrFail($product_id);

        return view('buy', compact('product'));
    }

    public function confirm(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));

        $user = User::firstOrCreate([
            'email' => $request->input('email'),
        ], [
            'name' => $request->input('name'),
            'password' => Str::random(10),
            'address' => $request->input('address'),
        ]);

        auth()->login($user);

        $user->orders()->create([
            'product_id' => $product->id,
            'price' => $product->price,
        ]);

        return redirect()->route('checkout');
    }

    public function checkout()
    {
        $order = Order::with('product')
            ->where('user_id', auth()->id())
            ->whereNull('paid_at')
            ->latest()
            ->firstOrFail();

        $paymentIntent = auth()->user()->createSetupIntent();

        return view('checkout', compact('order', 'paymentIntent'));
    }

    public function pay(Request $request)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($request->input('order_id'));
        $user = auth()->user();
        $paymentMethod = $request->input('payment_method');
        try {
            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($paymentMethod);
            $user->invoiceFor($order->product->name, $order->price);
        } catch (\Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }

        return redirect()->route('success');
    }
}
