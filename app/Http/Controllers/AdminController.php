<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
     /*____________Admin Dashboard____________*/
    /*_______________________________________*/
    public function index(){
        return view("admin.index");
    }

     /*____________Brands____________*/
     /*_____________________________*/
    public function brands()
    {   $brands = Brand::orderBy("id","desc")->paginate(5);
        return view('admin.brands',['brands'=>$brands]);
    }
    public function add_brand()
    {
        return view('admin.add-brand');
    }
    
    
    public function brand_store(Request $request){
        // Validate the incoming request data
        $request->validate([
            'name' => 'required', // The 'name' field is required
            'slug' => 'required|unique:brands,slug', // The 'slug' field is required and must be unique in the 'brands' table
            'image' => 'mimes:png,jpg,jpeg|max:2048' // The 'image' field must be a PNG, JPG, or JPEG file and no larger than 2MB
        ]);
    
        // Create a new instance of the Brand model
        $brand = new Brand();
    
        // Assign the 'name' value from the request to the 'name' attribute of the Brand model
        $brand->name = $request->name;
    
        // Generate a URL-friendly slug from the 'name' field and assign it to the 'slug' attribute of the Brand model
        $brand->slug = Str::slug($request->name);
    
        // Retrieve the uploaded image file from the request
        $image = $request->file('image');
    
        // Get the file extension of the uploaded image (e.g., png, jpg, jpeg)
        $file_extension = $request->file('image')->extension();
    
        // Generate a unique file name using the current timestamp and the file extension
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
    
        // Call the GenerateBrandThumbailsImage method to process and save the uploaded image as a thumbnail
        $this->GenerateBrandThumbailsImage($image, $file_name);
    
        // Assign the generated file name to the 'image' attribute of the Brand model
        $brand->image = $file_name;
    
        // Save the Brand model instance to the database
        $brand->save();
    
        // Redirect the user to the 'admin.brand.add' route with a success message
        return redirect()->route('admin.brand.add')->with('success', 'Brand has been added successfully');
    }
    
    public function GenerateBrandThumbailsImage($image, $imageName){
        // Define the destination path where the processed image will be saved
        $destinationPath = public_path('uploads/brands');
    
        // Read the uploaded image file using an image processing library (e.g., Intervention Image)
        $img = Image::read($image->path());
    
        // Resize the image to fit within a 124x124 pixel box, prioritizing the top part of the image
        $img->cover(124, 124, "top");
    
        // Resize the image to 124x124 pixels while maintaining the aspect ratio
        $img->resize(124, 124, function($constraint){
            $constraint->aspectRatio(); // Ensure the aspect ratio is preserved to prevent distortion
        })->save($destinationPath . '/' . $imageName); // Save the processed image to the destination path with the provided file name
    }

    public function brand_edit($id){
        $brand = Brand::find($id);
        return view('admin.brand-edit',['brand'=>$brand]);
    }
    public function brand_update(Request $request){
        // Validate the incoming request data
        $request->validate([
            'name' => 'required', // The 'name' field is required
            'slug' => 'required|unique:brands,slug,'.$request->id, // The 'slug' field is required and must be unique in the 'brands' table, ignoring the current record
            'image' => 'mimes:png,jpg,jpeg|max:2048' // The 'image' field must be a PNG, JPG, or JPEG file and no larger than 2MB
        ]);
    
        // Find the existing brand by its ID
        $brand = Brand::find($request->id);
    
        // If the brand is not found, redirect back with an error message
        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found');
        }
    
        // Update the 'name' field with the value from the request
        $brand->name = $request->name;
    
        // Update the 'slug' field with a URL-friendly version of the 'name' field
        $brand->slug = Str::slug($request->name);
    
        // Check if a new image file is uploaded
        if ($request->hasFile('image')) {
            // Delete the old image file if it exists
            if (File::exists(public_path('uploads/brands').'/'.$brand->image)) {
                File::delete(public_path('uploads/brands').'/'.$brand->image);
            }
    
            // Retrieve the uploaded image file from the request
            $image = $request->file('image');
    
            // Get the file extension of the uploaded image (e.g., png, jpg, jpeg)
            $file_extension = $request->file('image')->extension();
    
            // Generate a unique file name using the current timestamp and the file extension
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
    
            // Call the GenerateBrandThumbailsImage method to process and save the uploaded image as a thumbnail
            $this->GenerateBrandThumbailsImage($image, $file_name);
    
            // Assign the generated file name to the 'image' attribute of the Brand model
            $brand->image = $file_name;
        }
    
        // Save the updated Brand model instance to the database
        $brand->save();
    
        // Redirect the user to the 'admin.brands' route with a success message
        return redirect()->route('admin.brands')->with('success', 'Brand has been updated successfully');
    }

    public function brand_delete($id){
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands').'/'.$brand->image)) {
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('success', 'Brand has been deleted successfully');

    }

     /*____________Catgories____________*/
    /*_________________________________*/
    public function categories(){
        $categories = Category::orderBy('id',"DESC")->paginate();
        return view('admin.categories',['categories'=>$categories]);
    }
    public function add_category()
    {
        return view('admin.add-category');
    }
   
    public function category_store(Request $request){
        $request->validate([
            'name' => 'required', // The 'name' field is required
            'slug' => 'required|unique:categories,slug', // The 'slug' field is required and must be unique in the 'brands' table
            'image' => 'mimes:png,jpg,jpeg|max:2048' // The 'image' field must be a PNG, JPG, or JPEG file and no larger than 2MB
        ]);
        $category = new Category();
    
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateCategoryThumbailsImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.category.add')->with('success', 'Category has been added successfully');
    }
    public function GenerateCategoryThumbailsImage($image, $imageName){
        $destinationPath = public_path('uploads/categories');
            $img = Image::read($image->path());
            $img->cover(124, 124, "top");
            $img->resize(124, 124, function($constraint){
            $constraint->aspectRatio(); 
        })->save($destinationPath . '/' . $imageName); 
    }
    public function category_delete($id){
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('success', 'Category has been deleted successfully');

    }
    public function category_edit($id){
        $category = Category::find($id);
        return view('admin.category-edit',['category'=>$category]);
    }
    public function category_update(Request $request){

        $request->validate([
            'name' => 'required', 
            'slug' => 'required|unique:categories,slug,'.$request->id, 
            'image' => 'mimes:png,jpg,jpeg|max:2048' 
        ]);
    
        // Find the existing brand by its ID
       $category = Category::find($request->id);
        if (!$category) {
            return redirect()->route('admin.categories')->with('error', 'Category not found');
        }
       $category->name = $request->name;
       $category->slug = Str::slug($request->name);
        // Check if a new image file is uploaded
        if ($request->hasFile('image')) {
            // Delete the old image file if it exists
            if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateCategoryThumbailsImage($image, $file_name);
           $category->image = $file_name;
        }
       $category->save();
        return redirect()->route('admin.categories')->with('success', 'Category has been updated successfully');
    }

    /*____________Products____________*/
   /*________________________________*/
    public function products(){
        $products = Product::orderBy('id','DESC')->paginate(10);
        return view('admin.products',['products'=>$products]);
    }
    public function product_add(){
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add',['categories'=>$categories,'brands'=>$brands]);
    }

    // Define the product_store method that handles storing a new product
    public function product_store(Request $request){
        
        // Validate the incoming request data with specific rules
        $request->validate([
            'name'=>'required',  // Product name is required
            'slug'=>'required|unique:products,slug',  // Slug is required and must be unique in products table
            'short_description'=>'required',  // Short description is required
            'description'=>'required',  // Full description is required
            'regular_price'=>'required',  // Regular price is required
            'sale_price'=>'required',  // Sale price is required
            'SKU'=>'required',  // SKU (Stock Keeping Unit) is required
            'stock_status'=>'required',  // Stock status is required
            'featured'=>'required',  // Featured status is required
            'quantity'=>'required',  // Quantity is required
            'image'=>'required|mimes:png,jpg,jpeg|max:2048',  // Image is required, must be png/jpg/jpeg, max 2MB
            'category_id'=>'required',  // Category ID is required
            'brand_id'=>'required',  // Brand ID is required
        ]);

        // Create a new Product model instance
        $product = new Product();
        
        // Assign values from the request to the product model
        $product->name = $request->name;  // Set product name
        $product->slug = Str::slug($request->name);  // Generate URL-friendly slug from name
        $product->short_description = $request->short_description;  // Set short description
        $product->description = $request->description;  // Set full description
        $product->regular_price = $request->regular_price;  // Set regular price
        $product->sale_price = $request->sale_price;  // Set sale price
        $product->SKU = $request->SKU;  // Set SKU
        $product->stock_status = $request->stock_status;  // Set stock status
        $product->featured = $request->featured;  // Set featured status
        $product->quantity = $request->quantity;  // Set quantity
        $product->category_id = $request->category_id;  // Set category ID
        $product->brand_id = $request->brand_id;  // Set brand ID

        // Get current timestamp for unique filenames
        $current_timestamp = Carbon::now()->timestamp;

        // Handle main product image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');  // Get the uploaded image file
            $imageName = $current_timestamp.'.'.$image->extension();  // Create unique filename
            $this->GenerateProductThumbailsImage($image,$imageName);  // Generate thumbnails
            $product->image = $imageName;  // Save filename to product
        }

        // Initialize variables for gallery images
        $gallery_arr = array();  // Array to store gallery filenames
        $gallery_images = "";  // String to store comma-separated filenames
        $counter = 1;  // Counter for unique filenames

        // Handle gallery images upload if present
        if($request->hasFile('images')){
            $allowedFileExtention = ['jpg','png','jpeg'];  // Allowed file extensions
            $files = $request->file('images');  // Get all gallery images
            
            // Process each gallery image
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();  // Get file extension
                $gcheck = in_array($gextension,$allowedFileExtention);  // Check if extension is allowed
                
                if($gcheck){
                    // Create unique filename for gallery image
                    $gFileName = $current_timestamp.'.'.$counter.'.'.$gextension;
                    $this->GenerateProductThumbailsImage($file,$gFileName);  // Generate thumbnails
                    array_push($gallery_arr,$gFileName);  // Add filename to array
                    $counter +=1;  // Increment counter for next file
                }
            }
            
            // Convert array of filenames to comma-separated string
            $gallery_images = implode(',',$gallery_arr);
        }
        
        // Assign gallery images to product
        $product->images = $gallery_images;
        
        // Save the product to database
        $product->save();
        
        // Redirect back to products page with success message
        return redirect()->route('admin.product.add')->with('status','Product has been added succesfully');
    }

    // Helper method to generate product thumbnails
    public function GenerateProductThumbailsImage($image, $imageName){
        // Define paths for storing images
        $destinationPath_thumbnails = public_path('uploads/products/thumbnails');  // Thumbnail path
        $destinationPath = public_path('uploads/products');  // Main image path
        
        // Process the image using Image intervention
        $img = Image::read($image->path());
        
        // Create and save main product image (540x689)
        $img->cover(540, 689, "top");  // Crop image to exact dimensions
        $img->resize(540, 689, function($constraint){
            $constraint->aspectRatio();  // Maintain aspect ratio
        })->save($destinationPath . '/' . $imageName); 
        
        // Create and save thumbnail image (104x104)
        $img->resize(104, 104, function($constraint){
            $constraint->aspectRatio();  // Maintain aspect ratio
        })->save($destinationPath_thumbnails . '/' . $imageName); 
    }
    
    public function product_edit($id){
        $product = Product::find($id);
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit',['product'=>$product,'categories'=>$categories,'brands'=>$brands]);
    }
    public function product_update(Request $request){
        $request->validate([
            'name'=>'required',  // Product name is required
            'slug' => 'required|unique:products,slug,'.$request->id, 
            'short_description'=>'required',  // Short description is required
            'description'=>'required',  // Full description is required
            'regular_price'=>'required',  // Regular price is required
            'sale_price'=>'required',  // Sale price is required
            'SKU'=>'required',  // SKU (Stock Keeping Unit) is required
            'stock_status'=>'required',  // Stock status is required
            'featured'=>'required',  // Featured status is required
            'quantity'=>'required',  // Quantity is required
            'image'=>'mimes:png,jpg,jpeg|max:2048',  // Image is required, must be png/jpg/jpeg, max 2MB
            'category_id'=>'required',  // Category ID is required
            'brand_id'=>'required',  // Brand ID is required
        ]);
        $product = Product::find($request->id);
        // Assign values from the request to the product model
        $product->name = $request->name;  // Set product name
        $product->slug = Str::slug($request->name);  // Generate URL-friendly slug from name
        $product->short_description = $request->short_description;  // Set short description
        $product->description = $request->description;  // Set full description
        $product->regular_price = $request->regular_price;  // Set regular price
        $product->sale_price = $request->sale_price;  // Set sale price
        $product->SKU = $request->SKU;  // Set SKU
        $product->stock_status = $request->stock_status;  // Set stock status
        $product->featured = $request->featured;  // Set featured status
        $product->quantity = $request->quantity;  // Set quantity
        $product->category_id = $request->category_id;  // Set category ID
        $product->brand_id = $request->brand_id;  // Set brand ID

        // Get current timestamp for unique filenames
        $current_timestamp = Carbon::now()->timestamp;
        if ($request->hasFile('image')) {
            // Delete the old image file if it exists
            if (File::exists(public_path('uploads/products').'/'.$product->image)) {
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            // Delete the old image file if it exists in thumbnails
            if (File::exists(public_path('uploads/thumbnails').'/'.$product->image)) {
                File::delete(public_path('uploads/thumbnails').'/'.$product->image);
            }
            $image = $request->file('image');  // Get the uploaded image file
            $imageName = $current_timestamp.'.'.$image->extension();  // Create unique filename
            $this->GenerateProductThumbailsImage($image,$imageName);  // Generate thumbnails
            $product->image = $imageName;  // Save filename to product
        }

        // Initialize variables for gallery images
        $gallery_arr = array();  // Array to store gallery filenames
        $gallery_images = "";  // String to store comma-separated filenames
        $counter = 1;  // Counter for unique filenames

        // Handle gallery images upload if present
        if($request->hasFile('images')){
            foreach(explode(',',$product->images) as $ofile){
                // Delete the old image file if it exists
                if (File::exists(public_path('uploads/products').'/'.$ofile)) {
                    File::delete(public_path('uploads/products').'/'.$ofile);
                }
                // Delete the old image file if it exists in thumbnails
                if (File::exists(public_path('uploads/thumbnails').'/'.$ofile)) {
                    File::delete(public_path('uploads/thumbnails').'/'.$ofile);
                }
            }
            $allowedFileExtention = ['jpg','png','jpeg'];  // Allowed file extensions
            $files = $request->file('images');  // Get all gallery images
            
            // Process each gallery image
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();  // Get file extension
                $gcheck = in_array($gextension,$allowedFileExtention);  // Check if extension is allowed
                
                if($gcheck){
                    // Create unique filename for gallery image
                    $gFileName = $current_timestamp.'.'.$counter.'.'.$gextension;
                    $this->GenerateProductThumbailsImage($file,$gFileName);  // Generate thumbnails
                    array_push($gallery_arr,$gFileName);  // Add filename to array
                    $counter +=1;  // Increment counter for next file
                }
            }
            
            // Convert array of filenames to comma-separated string
            $gallery_images = implode(',',$gallery_arr);
            // Assign gallery images to product
            $product->images = $gallery_images;
        
        }
        // Save the product to database
        $product->save();
        return redirect()->route('admin.products')->with('status','Product has been updated succesfully');
    }
    public function product_delete($id){
        $product = Product::find($id);
         // Delete the old image file if it exists
         if (File::exists(public_path('uploads/products').'/'.$product->image)) {
            File::delete(public_path('uploads/products').'/'.$product->image);
        }
        // Delete the old image file if it exists in thumbnails
        if (File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)) {
            File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
        }
        foreach(explode(',',$product->images) as $ofile){
            // Delete the old image file if it exists
            if (File::exists(public_path('uploads/products').'/'.$ofile)) {
                File::delete(public_path('uploads/products').'/'.$ofile);
            }
            // Delete the old image file if it exists in thumbnails
            if (File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)) {
                File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status','Product has been deleted succesfully');

    }

    /*____________Coupons_____________*/
   /*________________________________*/
    public function coupons(){
        $coupons = Coupon::orderBy('expiry_date','DESC')->paginate(12);
        return view('admin.coupons',compact('coupons'));
    }
    public function coupon_add(){
        return view('admin.add-coupon');
    }

    public function coupon_store(Request $request){
        $request->validate([
            'code'=>'required|unique:coupons,code',
            'type'=>'required',
            'value'=>'required|numeric',
            'cart_value'=>'required|numeric',
            'expiry_date'=>'required|date'
        ]);
        
        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        
        return redirect()->route('admin.coupon.add')->with('status','Coupon has been added successfully');
    }
    public function coupon_edit($id){
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit',compact('coupon'));
    }
    public function coupon_update(Request $request){
        $request->validate([
            'code'=>'required|unique:coupons,code,'.$request->id,
            'type'=>'required',
            'value'=>'required|numeric',
            'cart_value'=>'required|numeric',
            'expiry_date'=>'required|date'
        ]);
        
        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        
        return redirect()->route('admin.coupons')->with('status','Coupon has been updated successfully');
    }
    public function coupon_delete($id){
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status','Coupon has been deleted successfully');

    }
}
