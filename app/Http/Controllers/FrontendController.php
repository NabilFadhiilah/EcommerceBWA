<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    //
    public function index(Request $request)
    {
        # code...
        $products = Product::with(['galleries'])->latest()->get();
        return view('pages.frontend.index', compact('products'));
    }
    public function details(Request $request, $slug)
    {
        # code...
        $products = Product::with(['galleries'])->where('slug', $slug)->firstOrFail();
        $recomendations = Product::with(['galleries'])->inRandomOrder()->limit(4)->get();
        return view('pages.frontend.details', compact('products', 'recomendations'));
    }

    public function cartAdd(Request $request, $id)
    {
        # code...
        Cart::create([
            'users_id' => Auth::user()->id,
            'products_id' => $id,
        ]);
        return redirect('cart');
    }

    public function cart(Request $request)
    {
        # code...
        $carts = Cart::with([
            'product.galleries'
        ])->where('users_id', Auth::user()->id)->get();
        return view('pages.frontend.cart', compact('carts'));
    }

    public function cartDelete(Request $request, $id)
    {
        # code...
        $item = Cart::findOrFail($id);
        $item->delete();
        return redirect('cart');
    }
    public function success(Request $request)
    {
        # code...
        return view('pages.frontend.success');
    }
}
