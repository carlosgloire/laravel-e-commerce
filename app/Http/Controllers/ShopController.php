<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request) {
        $size = $request->query('size') ? $request->query('size') : 12;
        $o_column = "id";
        $o_order = "DESC";
        $order = $request->query('order') ? $request->query('order') : -1;
        $f_brands = $request->query('brands', ''); // Default to empty string
        $f_categories = $request->query('categories', '');
        $min_price = $request->query('min') ? $request->query('min') : 1;
        $max_price = $request->query('max') ? $request->query('max') : 500;
        // Sorting logic
        switch($order) {
            case 1:
                $o_column = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $o_column = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $o_column = 'sale_price';
                $o_order = 'ASC';
                break;
            case 4:
                $o_column = 'sale_price';
                $o_order = 'DESC';
                break;
        }
    
        $brands = Brand::orderBy('name','ASC')->get();
        $categories = Category::orderBy('name','ASC')->get();
        // Product query with improved brand filtering
        $products = Product::when($f_brands, function($query) use ($f_brands) {
            $query->whereIn('brand_id', explode(',', $f_brands));
        })
        ->when($f_categories, function($query) use ($f_categories) {
            $query->whereIn('category_id', explode(',', $f_categories));
        })
        ->where(function($query) use ($min_price, $max_price) {
            $query->where(function($q) use ($min_price, $max_price) {
                $q->whereBetween('regular_price', [$min_price, $max_price])
                  ->where('regular_price', '>', 0);
            })->orWhere(function($q) use ($min_price, $max_price) {
                $q->whereBetween('sale_price', [$min_price, $max_price])
                  ->where('sale_price', '>', 0);
            });
        })
        ->orderBy($o_column, $o_order)
        ->paginate($size);
        
        return view('shop.index', compact('products', 'size', 'order', 'brands', 'f_brands','categories','f_categories','min_price','max_price'));
    }
    public function product_detail($product_slug){
        $product = Product::where('slug',$product_slug)->first();
        $related_products = Product::where('slug','<>',$product_slug)->get()->take(8); //Select 8 related product where the slug is not equal to the first product
        return view('shop.details',compact('product','related_products'));
    }
}
