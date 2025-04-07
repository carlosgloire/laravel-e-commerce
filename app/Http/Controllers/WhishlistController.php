<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class WhishlistController extends Controller
{
    public function index(){
        $items = Cart::instance('whishlist')->content();
        return view('whishlist.index',compact('items'));
    }
    public function add_to_whishlist(Request $request){
        Cart::instance('whishlist')->add($request->id,$request->name,$request->quantity,$request->price)->associate('App\Models\Product');
        return redirect()->back();
    }
    public function remove_item($rowId){
        Cart::instance('whishlist')->remove($rowId);
        return redirect()->back();
    }
    public function empty_whishlist(){
        Cart::instance('whishlist')->destroy();
        return redirect()->back();
    }
    public function move_to_cart($rowId){
        $item = Cart::instance('whishlist')->get($rowId);
        Cart::instance('whishlist')->remove($rowId);
        Cart::instance('cart')->add($item->id,$item->name,$item->qty,$item->price)->associate('App\Models\Product');
        return redirect()->back();
    }
}
