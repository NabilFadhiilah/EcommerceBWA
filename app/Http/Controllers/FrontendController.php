<?php

namespace App\Http\Controllers;

use Exception;
use Midtrans\Snap;
use App\Models\Cart;
use Midtrans\Config;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckoutRequest;

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

    public function checkout(CheckoutRequest $request)
    {
        # code...
        $data = $request->all();
        // die;
        // get carts data
        $carts = Cart::with(['product'])->where('users_id', Auth::user()->id)->get();

        // add to transaction data
        $data['users_id'] = Auth::user()->id;
        $data['total_price'] = $carts->sum('product.price');
        // create transaction
        $transaction = Transaction::create($data);
        // create transaction item
        foreach ($carts as $cart) {
            # code...
            $items[] = Transaction::create([
                'transaction_id' => $transaction->id,
                'users_id' => $cart->users_id,
                'products_id' => $cart->products_id
            ]);
        }
        // delete cart after transasction
        Cart::where('users_id', Auth::user()->id)->delete();
        // configure midtrans
        // Set your Merchant Server Key
        Config::$serverKey = config('services.midtrans.serverKey');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = config('services.midtrans.isProduction');
        // Set sanitization on (default)
        Config::$isSanitized = config('services.midtrans.isSanitized');
        // Set 3DS transaction for credit card to true
        Config::$is3ds = config('services.midtrans.is3ds');
        // setup midtrans vars
        $midtrans = [
            'transaction_details' => [
                'order_id' => 'LUX-' . $transaction->id,
                'gross_amount' => (int) $transaction->total_price,
            ],
            'costumer_details' => [
                'first_name' => $transaction->name,
                'email' => $transaction->email
            ],
            'enabled_payments' => ['gopay', 'bank_transfer'],
            'vtweb' => [],
        ];
        // payment process
        try {
            // Get Snap Payment Page URL
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;
            $transaction->save();
            return redirect($paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
