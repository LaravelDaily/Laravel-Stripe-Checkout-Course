<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
}
