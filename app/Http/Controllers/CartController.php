<?php

// Define the namespace for this controller
namespace App\Http\Controllers;

// Import required models and classes
use App\Models\Address; // Address model
use App\Models\Coupon; // Coupon model
use App\Models\Order; // Order model
use App\Models\OrderItem; // OrderItem model
use App\Models\Transaction; // Transaction model
use Carbon\Carbon; // Date handling library
use Illuminate\Support\Facades\Session; // Session facade
use Illuminate\Http\Request; // HTTP request class
use Illuminate\Support\Facades\Auth; // Authentication facade
use Surfsidemedia\Shoppingcart\Facades\Cart; // Shopping cart facade

// Cart controller class
class CartController extends Controller
{
    // Display cart contents
    public function index(){
        $items = Cart::instance('cart')->content(); // Get all cart items
        return view('cart.index',['items'=>$items]); // Return cart view with items
    }

    // Add product to cart
    public function add_to_cart(Request $request){
        Cart::instance('cart')->add($request->id,$request->name,$request->quantity,$request->price)->associate('App\Models\Product'); // Add item to cart
        return redirect()->back(); // Redirect back
    }

    // Increase item quantity in cart
    public function increase_cart_quantity($rowId){
        $product = Cart::instance('cart')->get($rowId); // Get cart item
        $qty = $product->qty + 1; // Increment quantity
        Cart::instance('cart')->update($rowId,$qty); // Update cart
        return redirect()->back(); // Redirect back
    }

    // Decrease item quantity in cart
    public function decrease_cart_quantity($rowId){
        $product = Cart::instance('cart')->get($rowId); // Get cart item
        $qty = $product->qty - 1; // Decrement quantity
        Cart::instance('cart')->update($rowId,$qty); // Update cart
        return redirect()->back(); // Redirect back
    }

    // Remove item from cart
    public function remove_item($rowId){
        Cart::instance('cart')->remove($rowId); // Remove item from cart
        return redirect()->back(); // Redirect back
    }

    // Empty the entire cart
    public function empty_cart(){
        Cart::instance('cart')->destroy(); // Clear cart
        return redirect()->back(); // Redirect back
    }

    // Apply coupon code
    public function apply_coupon_code(Request $request)
    {
        $coupon_code = $request->coupon_code; // Get coupon code from request
        
        if(empty($coupon_code)) { // Check if coupon code is empty
            return redirect()->back()->with('error', 'Please enter a coupon code!'); // Return error
        }

        $coupon = Coupon::where('code', $coupon_code)->first(); // Find coupon in database
        
        if(!$coupon) { // Check if coupon exists
            return redirect()->back()->with('error', 'Coupon code not found!'); // Return error
        }

        if($coupon->expiry_date < Carbon::today()) { // Check if coupon is expired
            return redirect()->back()->with('error', 'This coupon has expired!'); // Return error
        }

        $cartSubtotal = Cart::instance('cart')->subtotal(); // Get cart subtotal
        if($coupon->cart_value > $cartSubtotal) { // Check minimum cart value
            return redirect()->back()->with('error', 'Minimum cart value for this coupon is $'.$coupon->cart_value); // Return error
        }

        Session::put('coupon', [ // Store coupon in session
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'cart_value' => $coupon->cart_value
        ]); 
        
        $this->calculateDiscount(); // Calculate discount
        return redirect()->back()->with('success', 'Coupon applied successfully!'); // Return success
    }

    // Calculate discount based on coupon
    public function calculateDiscount()
    {
        $discount = 0; // Initialize discount
        
        if(Session::has('coupon')) { // Check if coupon exists in session
            $coupon = Session::get('coupon'); // Get coupon from session
            
            if($coupon['type'] == 'fixed') { // Check coupon type
                $discount = $coupon['value']; // Fixed amount discount
            } else {
                $discount = (Cart::instance('cart')->subtotal() * $coupon['value']) / 100; // Percentage discount
            }
            
            $subtotalAfterDiscount = Cart::instance('cart')->subtotal() - $discount; // Calculate subtotal after discount
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100; // Calculate tax
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount; // Calculate total

            Session::put('discounts', [ // Store discount details in session
                'code' => $coupon['code'],
                'discount' => number_format(floatval($discount), 2, '.', ''),
                'subtotal' => number_format(floatval($subtotalAfterDiscount), 2, '.', ''),
                'tax' => number_format(floatval($taxAfterDiscount), 2, '.', ''),
                'total' => number_format(floatval($totalAfterDiscount), 2, '.', ''),
            ]);
        }
    }

    // Remove coupon code
    public function remove_coupon_code(){
        Session::forget('coupon'); // Remove coupon from session
        Session::forget('discounts'); // Remove discounts from session
        return redirect()->back()->with('success','Coupon has been removed'); // Return success
    }

    // Checkout process
    public function checkout(){
        if(!Auth::check()){ // Check if user is authenticated
            return redirect()->route('login'); // Redirect to login if not
        }
        $address = Address::where('user_id',Auth::user()->id)->where('is_default',1)->first(); // Get default address
        return view('checkout',compact('address')); // Return checkout view with address
    }

    // Place an order
    public function place_an_order(Request $request){
        $user_id = Auth::user()->id; // Get current user ID
        $address = Address::where('user_id',$user_id)->where('is_default',true)->first(); // Get default address
        
        if(!$address){ // If no default address exists
            $request->validate([ // Validate address fields
                'name'=>'required|max:100',
                'phone'=>'required|numeric|digits:10',
                'zip'=>'required|numeric|digits:10',
                'state'=>'required',
                'city'=>'required',
                'address'=>'required',
                'locality'=>'required',
                'landmark'=>'required'
            ]);
            
            $address = new Address(); // Create new address
            $address->name =$request->name; // Set name
            $address->phone =$request->phone; // Set phone
            $address->zip =$request->zip; // Set zip
            $address->state =$request->state; // Set state
            $address->city =$request->city; // Set city
            $address->address =$request->address; // Set address
            $address->locality =$request->locality; // Set locality
            $address->landmark =$request->landmark; // Set landmark
            $address->country = "Rwanda"; // Set country
            $address->user_id = $user_id; // Set user ID
            $address->is_default = true; // Set as default
            $address->save(); // Save address
        }
        
        $this->setAmountforCheckout(); // Calculate order amounts

        $order = new Order(); // Create new order
        $order->user_id = $user_id; // Set user ID
        $order->subtotal = Session::get('checkout')['subtotal']; // Set subtotal
        $order->discount = Session::get('checkout')['discount']; // Set discount
        $order->tax = Session::get('checkout')['tax']; // Set tax
        $order->total = Session::get('checkout')['total']; // Set total
        $order->name = $address->name; // Set name
        $order->phone = $address->phone; // Set phone
        $order->locality = $address->locality; // Set locality
        $order->address = $address->address; // Set address
        $order->city = $address->city; // Set city
        $order->state = $address->state; // Set state
        $order->country = $address->country; // Set country
        $order->landmark = $address->landmark; // Set landmark
        $order->zip = $address->zip; // Set zip
        $order->save(); // Save order

        // Add cart items to order items
        foreach (Cart::instance('cart')->content() as $item){
            $orderItem = new OrderItem(); // Create new order item
            $orderItem->product_id = $item->id; // Set product ID
            $orderItem->order_id = $order->id; // Set order ID
            $orderItem->price = $item->price; // Set price
            $orderItem->quantity = $item->qty; // Set quantity
            $orderItem->save(); // Save order item
        }
        
        // Handle payment methods
        if($request->mode == 'card'){
            // Card payment logic would go here
        }
        elseif($request->mode == 'paypal'){
            // PayPal payment logic would go here
        }
        elseif($request->mode == 'cod'){ // Cash on delivery
            $transaction = new Transaction(); // Create new transaction
            $transaction->user_id = $user_id; // Set user ID
            $transaction->order_id = $order->id; // Set order ID
            $transaction->mode = $request->mode; // Set payment mode
            $transaction->status = "pending"; // Set status
            $transaction->save(); // Save transaction
        }
        
        Cart::instance('cart')->destroy(); // Clear cart
        Session::forget('checkout'); // Remove checkout data from session
        Session::forget('coupon'); // Remove coupon from session
        Session::forget('discounts'); // Remove discounts from session
        Session::put('order_id',$order->id); // Store order ID in session
        return redirect()->route('cart.order.confirmation'); // Redirect to confirmation
    }

    // Calculate amounts for checkout
    public function setAmountforCheckout(){
        if(Cart::instance('cart')->content()->count() == 0){ // Check if cart is empty
            Session::forget('checkout'); // Clear checkout data
            return; // Exit function
        }
        
        if(Session::has('coupon')){ // Check if coupon is applied
            Session::put('checkout', [ // Store checkout data with discount
                'discount' => Session::get('discounts')['discount'],
                'subtotal' => Session::get('discounts')['subtotal'],
                'total' => Session::get('discounts')['total'],
                'tax' => Session::get('discounts')['tax'],
            ]);
        } else { // No coupon applied
            Session::put('checkout', [ // Store checkout data without discount
                'discount' => 0, // No discount
                'subtotal' => (float)str_replace(',', '', Cart::instance('cart')->subtotal()), // Format subtotal
                'total' => (float)str_replace(',', '', Cart::instance('cart')->total()), // Format total
                'tax' => (float)str_replace(',', '', Cart::instance('cart')->tax()) // Format tax
            ]);
        }
    }

    // Show order confirmation
    public function order_confirmation(){
        if(Session::has('order_id')){ // Check if order ID exists in session
            $order = Order::find(Session::get('order_id')); // Get order
            return view('order-confirmation',compact('order')); // Show confirmation with order
        }
        return redirect()->route('cart.index'); // Redirect to cart if no order
    }
}