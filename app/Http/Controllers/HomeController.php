<?php

namespace App\Http\Controllers;
use App\Models\Slide;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
class HomeController extends Controller
{
    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $categories = Category::orderBy('name','ASC')->get();
        $sale_products = Product::whereNotNull('sale_price')->where('sale_price','<>','')->get()->take(8);
        $slides = Slide::where('status',1)->get()->take(3);
        $featured_products = Product::where('featured',1)->get()->take(8);
        return view('home.index',compact('slides','categories','sale_products','featured_products'));
    }
    public function contact(){
        return view('home.contact');
    }
    public function contact_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10',
            'comment' => 'required',
        ]);

        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->comment = $request->comment;
        $contact->save();

        // Send email to admin
        Mail::to('ndayisabarenzaho@gmail.com')->send(new ContactMessageMail($contact));

        return redirect()->back()->with('success', 'Your message has been sent successfully!');
    }

    public function search(Request $request){
        $query = $request->input('query');
        $results = Product::where('name','LIKE',"%{$query}%")->get()->take(8);
        return response()->json($results);
    }
}
