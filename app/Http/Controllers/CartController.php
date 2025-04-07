<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Surfsidemedia\Shoppingcart\Facades\Cart;
class CartController extends Controller
{
    public function index(){
        $items = Cart::instance('cart')->content();
        return view('cart.index',['items'=>$items]);
    }
    public function add_to_cart(Request $request){
        Cart::instance('cart')->add($request->id,$request->name,$request->quantity,$request->price)->associate('App\Models\Product');
        return redirect()->back();
    }
    public function increase_cart_quantity($rowId){
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty + 1;
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }
    public function decrease_cart_quantity($rowId){
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty - 1;
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }
    public function remove_item($rowId){
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }
    public function empty_cart(){
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }
    public function apply_coupon_code(Request $request)
{
    $coupon_code = $request->coupon_code;
    
    if(empty($coupon_code)) {
        return redirect()->back()->with('error', 'Please enter a coupon code!');
    }

    // Debugging query - let's check each condition separately
    $coupon = Coupon::where('code', $coupon_code)->first();
    
    if(!$coupon) {
        return redirect()->back()->with('error', 'Coupon code not found!');
    }

    if($coupon->expiry_date < Carbon::today()) {
        return redirect()->back()->with('error', 'This coupon has expired!');
    }

    $cartSubtotal = Cart::instance('cart')->subtotal();
    if($coupon->cart_value > $cartSubtotal) {
        return redirect()->back()->with('error', 
            'Minimum cart value for this coupon is $'.$coupon->cart_value);
    }

    // If we get here, all conditions are met
    Session::put('coupon', [
        'code' => $coupon->code,
        'type' => $coupon->type,
        'value' => $coupon->value,
        'cart_value' => $coupon->cart_value
    ]); 
    
    $this->calculateDiscount();
    return redirect()->back()->with('success', 'Coupon applied successfully!');
}

    public function calculateDiscount()
    {
        $discount = 0;
        
        if(Session::has('coupon')) {
            $coupon = Session::get('coupon');
            
            if($coupon['type'] == 'fixed') {
                $discount = $coupon['value'];
            } else {
                $discount = (Cart::instance('cart')->subtotal() * $coupon['value']) / 100;
            }
            
            $subtotalAfterDiscount = Cart::instance('cart')->subtotal() - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            Session::put('discounts', [
                'code' => $coupon['code'],
                'discount' => number_format(floatval($discount), 2, '.', ''),
                'subtotal' => number_format(floatval($subtotalAfterDiscount), 2, '.', ''),
                'tax' => number_format(floatval($taxAfterDiscount), 2, '.', ''),
                'total' => number_format(floatval($totalAfterDiscount), 2, '.', ''),
            ]);
        }
    }
    public function remove_coupon_code(){
        Session::forget('coupon');
        Session::forget('discounts');
        return redirect()->back()->with('success','Coupon has been removed');
    }
    public function checkout(){
        //check if the user is connected
        if(!Auth::check()){
            return redirect()->route('login');
        }
        $address =  Address::where('user_id',Auth::user()->id)->where('is_default',1)->first();
        return view('checkout',compact('address'));
    }
    public function place_an_order(Request $request){
        $user_id = Auth::user()->id;
        $address = Address::where('user_id',$user_id)->where('is_default',true)->first();
        if(!$address){
            $request->validate([
                'name'=>'required|max:100',
                'phone'=>'required|numeric|digits:10',
                'zip'=>'required|numeric|digits:10',
                'state'=>'required',
                'city'=>'required',
                'address'=>'required',
                'locality'=>'required',
                'landmark'=>'required'

            ]);
            $address = new Address();
            $address->name =$request->name;
            $address->phone =$request->phone;
            $address->zip =$request->zip;
            $address->state =$request->state;
            $address->city =$request->city;
            $address->address =$request->address;
            $address->locality =$request->locality;
            $address->landmark =$request->landmark;
            $address->country = "Rwanda";
            $address->user_id = $user_id;
            $address->is_default = true;
            $address->save();
        }
        $this->setAmountforCheckout();

        $order = new Order();
        $order-> user_id = $user_id;
        $order->subtotal  = Session::get('checkout')['subtotal'];
        $order->discount = Session::get('checkout')['discount'];
        $order->tax = Session::get('checkout')['tax'];
        $order->total  = Session::get('checkout')['total'];
        $order->name = $address->name; 
        $order->phone = $address->phone; 
        $order->locality = $address->locality; 
        $order->address = $address->address; 
        $order->city = $address->city; 
        $order->state = $address->state; 
        $order->country = $address->country; 
        $order->landmark = $address->landmark; 
        $order->zip = $address->zip; 
        $order->save();

        //Insert the cart item into the order item tables 
        foreach (Cart::instance('cart')->content() as $item){
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();
        }
        if($request->mode == 'card'){
            //
        }
        elseif($request->mode == 'paypal'){
           // 
        }
        elseif($request->mode == 'cod'){
        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->order_id = $order->id;
        $transaction->mode= $request->mode;
        $transaction->status = "pending";
        $transaction->save();
        }
        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');

    }
    public function setAmountforCheckout(){
        //Check if the cart has item
        if(Cart::instance('cart')->content()->count() > 0){
            Session::forget('checkout');
            return;
        }
        if(Session::has('coupon')){
            Session::put('ckeckout',[
                'discount' => Session::get('discounts')['discount'],
                'subtotal' => Session::get('discounts')['subtotal'],
                'total' => Session::get('discounts')['total'],
                'tax' => Session::get('discounts')['tax'],
            ]);
        }else{
            Session::put('ckeckout',[
                'discount' => 0,
                'subtotal' => Cart::instance('cart')->subtotal(),
                'total' => Cart::instance('cart')->total(),
                'tax' => Cart::instance('cart')->tax(),
            ]);
        }
    }

}
