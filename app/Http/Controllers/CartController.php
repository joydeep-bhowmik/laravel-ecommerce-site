<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        $response = $user->addToCart($product->id, $request->size_name, $request->quantity);

        if ($response['status']) {
            return redirect()->to(route('carts'));
        }

        return back()->with('error', $response['message']);
    }
}
