<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    //
    public function index(Request $request)
    {
        # code...
        return view('pages.frontend.index');
    }
    public function details(Request $request, $slug)
    {
        # code...
        return view('pages.frontend.details');
    }
    public function cart(Request $request)
    {
        # code...
        return view('pages.frontend.cart');
    }
    public function success(Request $request)
    {
        # code...
        return view('pages.frontend.success');
    }
}
