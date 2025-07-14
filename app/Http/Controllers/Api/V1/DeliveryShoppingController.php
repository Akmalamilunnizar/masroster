<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeliveryShoppingController extends Controller
{
    public function index()
    {
        $cartItems = [/* ... */];
        $shippingOptions = [/* ... */];
        return view('admin.deliveryshopping', compact('cartItems', 'shippingOptions'));
    }
}
