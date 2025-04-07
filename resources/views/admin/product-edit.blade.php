<x-admin-layout title="Edit product">
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Add Product</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{route('admin.index')}}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <a href="{{route('admin.products')}}">
                            <div class="text-tiny">Products</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Edit product</div>
                    </li>
                </ul>
            </div>
            <!-- form-add-product -->
            <form class="tf-section-2 form-add-product"  method="POST" action="{{ route('admin.product.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if (Session::has('status'))
                        <p class="alert alert-success text-center">{{Session::get('status')}}</p>
                @endif
                <input type="hidden" name="id" value="{{$product->id}}">
                <div class="wg-box">
                    <fieldset class="name">
                        <div class="body-title mb-10">Product name <span class="tf-color-1">*</span>
                        </div>
                        <input class="mb-10" type="text" id="productName" placeholder="Enter product name" name="name" tabindex="0" value="{{$product->name}}" aria-required="true" required="">
                        <div class="text-tiny">Do not exceed 100 characters when entering the product name.</div>
                    </fieldset>
                    @error('name') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                    <fieldset class="name">
                        <div class="body-title mb-10">Slug <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" id="productSlug" placeholder="Enter product slug" name="slug" tabindex="0" value="{{$product->slug}}" aria-required="true" required="">
                        <div class="text-tiny">Do not exceed 100 characters when entering the product name.</div>
                    </fieldset>
                    @error('slug') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                    <div class="gap22 cols">
                        <fieldset class="category">
                            <div class="body-title mb-10">Category <span class="tf-color-1">*</span>
                            </div>
                            <div class="select">
                                <select class="" name="category_id">
                                    <option>Choose category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{$category->id}} "{{$product->category_id === $category->id ? 'selected':''}}>{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                        @error('category_id') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                        <fieldset class="brand">
                            <div class="body-title mb-10">Brand <span class="tf-color-1">*</span>
                            </div>
                            <div class="select">
                                <select class="" name="brand_id">
                                    <option>Choose Brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{$brand->id}}" {{$product->brand_id === $brand->id ? "selected" :""}}>{{$brand->name}}</option>
                                    @endforeach

                                </select>
                            </div>
                        </fieldset>
                        @error('brand_id') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                    </div>

                    <fieldset class="shortdescription">
                        <div class="body-title mb-10">Short Description <span
                                class="tf-color-1">*</span></div>
                        <textarea class="mb-10 ht-150" name="short_description" placeholder="Short Description" tabindex="0" aria-required="true" required="">{{$product->short_description}}</textarea>
                        <div class="text-tiny">Do not exceed 100 characters when entering the
                            product name.</div>
                    </fieldset>
                    @error('short_description') <span class="alert alert-success text-center">{{$message}}</span> @enderror

                    <fieldset class="description">
                        <div class="body-title mb-10">Description <span class="tf-color-1">*</span>
                        </div>
                        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true" required="">{{$product->description}}</textarea>
                        <div class="text-tiny">Do not exceed 100 characters when entering the product name.</div>
                    </fieldset>
                    @error('description') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                </div>
                <div class="wg-box">
                    <fieldset>
                        <div class="body-title">Upload images <span class="tf-color-1">*</span>
                        </div>
                        <div class="upload-image flex-grow">
                            @if ($product->image)
                                <div class="item" id="imgpreview" >
                                    <img src="/uploads/products/{{$product->image}}" class="effect8 previewImage" alt="Preview">
                                    <button type="button" class="remove-image" onclick="removeImage('imgpreview', 'myFile')">×</button>
                                </div>
                            @endif
                            
                            <div id="upload-file" class="item up-load">
                                <label class="uploadfile" for="myFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="body-text">Drop your images here or select <span
                                            class="tf-color">click to browse</span></span>
                                    <input type="file" id="myFile" name="image" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('image') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                    <fieldset>
                        <div class="body-title mb-10">Upload Gallery Images</div>
                        <div class="upload-image mb-16">
                            <div id="galleryPreviews" class="flex flex-wrap gap-10">
                                @if ($product->images)
                                    @foreach (explode(',',$product->images) as $img)
                                    <div class="gallery-preview-item relative">
                                        <img src="/uploads/products/{{trim($img)}}" class="effect8" alt="Gallery Preview" style="width: 100px; height: 100px; object-fit: cover;">
                                        <button type="button" class="remove-image" onclick="removeGalleryImage(this)">×</button>
                                    </div>
                                    
                                    @endforeach
                                @endif
                            </div>
                            <div id="galUpload" class="item up-load">
                                <label class="uploadfile" for="gFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="text-tiny">Drop your images here or select <span
                                            class="tf-color">click to browse</span></span>
                                    <input type="file" id="gFile" name="images[]" accept="image/*"
                                        multiple="">
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('images') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">Regular Price <span
                                    class="tf-color-1">*</span></div>
                            <input class="mb-10" type="text" placeholder="Enter regular price" name="regular_price" tabindex="0" value="{{$product->regular_price}}" aria-required="true"
                                required="">
                        </fieldset>
                        @error('regular_price') <span class="alert alert-success text-center">{{$message}}</span> @enderror

                        <fieldset class="name">
                            <div class="body-title mb-10">
                                Sale Price 
                                <span class="tf-color-1">*</span>
                            </div>
                            <input class="mb-10" type="text" placeholder="Enter sale price" name="sale_price" tabindex="0" value="{{$product->sale_price}}" aria-required="true"
                                required="">
                        </fieldset>
                        @error('sale_price') <span class="alert alert-success text-center">{{$message}}</span> @enderror

                    </div>
                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">SKU <span class="tf-color-1">*</span>
                            </div>
                            <input class="mb-10" type="text" placeholder="Enter SKU" name="SKU" tabindex="0" value="{{$product->SKU}}" aria-required="true" required="">
                        </fieldset>
                        @error('SKU') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                        <fieldset class="name">
                            <div class="body-title mb-10">Quantity <span class="tf-color-1">*</span>
                            </div>
                            <input class="mb-10" type="text" placeholder="Enter quantity" name="quantity" tabindex="0" value="{{$product->quantity}}" aria-required="true" required="">
                        </fieldset>
                        @error('quantity') <span class="alert alert-success text-center">{{$message}}</span> @enderror

                    </div>

                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">Stock</div>
                            <div class="select mb-10">
                                <select class="" name="stock_status">
                                    <option value="instock" {{$product->stock_status =="instock" ? "selected" :""}}>InStock</option>
                                    <option value="outofstock" {{$product->stock_status =="outofstock" ? "selected" :""}}>Out of Stock</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('stock_status') <span class="alert alert-success text-center">{{$message}}</span> @enderror
                        <fieldset class="name">
                            <div class="body-title mb-10">Featured</div>
                            <div class="select mb-10">
                                <select class="" name="featured">
                                    <option value="0"{{$product->featured =="0" ? "selected" :""}}>No</option>
                                    <option value="1"{{$product->featured =="1" ? "selected" :""}}>Yes</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('featured') <span class="alert alert-success text-center">{{$message}}</span> @enderror

                    </div>
                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">update product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productNameInput = document.getElementById('productName');
            const productSlugInput = document.getElementById('productSlug');
            const imageInput = document.getElementById('myFile');
            const galleryInput = document.getElementById('gFile');
            const uploadFileDiv = document.getElementById('upload-file');
            const galUploadDiv = document.getElementById('galUpload');
            const galleryPreviews = document.getElementById('galleryPreviews');

            // Generate slug from product name
            productNameInput.addEventListener('input', function() {
                const name = productNameInput.value.trim();
                const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                productSlugInput.value = slug;
            });

            // Preview uploaded main image
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    previewImageFile(file, 'imgpreview');
                }
            });

            // Preview uploaded gallery images
            galleryInput.addEventListener('change', function() {
                const files = this.files;
                if (files) {
                    galleryPreviews.innerHTML = ''; // Clear previous previews
                    Array.from(files).forEach(file => {
                        previewGalleryImage(file);
                    });
                }
            });

            // Drag and drop functionality for main image
            setupDragAndDrop(uploadFileDiv, imageInput, 'imgpreview');

            // Drag and drop functionality for gallery images
            setupDragAndDrop(galUploadDiv, galleryInput, null, true);

            // Function to preview the main image
            function previewImageFile(file, previewId) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.getElementById(previewId);
                    const img = previewDiv.querySelector('img');
                    img.src = e.target.result;
                    previewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            // Function to preview gallery images
            function previewGalleryImage(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'gallery-preview-item relative';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" class="effect8" alt="Gallery Preview" style="width: 100px; height: 100px; object-fit: cover;">
                        <button type="button" class="remove-image" onclick="removeGalleryImage(this)">×</button>
                    `;
                    galleryPreviews.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }

            // Setup drag and drop for an element
            function setupDragAndDrop(dropZone, inputElement, previewId, isMultiple = false) {
                dropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    dropZone.classList.add('dragover');
                });

                dropZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                });

                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                    const files = e.dataTransfer.files;
                    
                    if (files.length > 0) {
                        if (files[0].type.startsWith('image/')) {
                            if (isMultiple) {
                                // For gallery images
                                const dataTransfer = new DataTransfer();
                                // Add existing files
                                if (inputElement.files) {
                                    for (let i = 0; i < inputElement.files.length; i++) {
                                        dataTransfer.items.add(inputElement.files[i]);
                                    }
                                }
                                // Add new files
                                for (let i = 0; i < files.length; i++) {
                                    if (files[i].type.startsWith('image/')) {
                                        dataTransfer.items.add(files[i]);
                                    }
                                }
                                inputElement.files = dataTransfer.files;
                                
                                // Update previews
                                galleryPreviews.innerHTML = '';
                                for (let i = 0; i < inputElement.files.length; i++) {
                                    previewGalleryImage(inputElement.files[i]);
                                }
                            } else {
                                // For single image
                                inputElement.files = files;
                                previewImageFile(files[0], previewId);
                            }
                        } else {
                            alert('Please upload a valid image file.');
                        }
                    }
                });
            }
        });

        // Remove main image
        function removeImage(previewId, inputId) {
            document.getElementById(previewId).style.display = 'none';
            document.getElementById(inputId).value = '';
        }

        // Remove gallery image
        function removeGalleryImage(button) {
            const previewDiv = button.parentElement;
            previewDiv.remove();
            
            // Update the file input
            const galleryInput = document.getElementById('gFile');
            const dataTransfer = new DataTransfer();
            const previews = document.querySelectorAll('.gallery-preview-item');
            
            // Recreate the FileList with remaining files
            previews.forEach(preview => {
                // This is a simplified approach - in a real app you might need to maintain 
                // a mapping between previews and files or use a more sophisticated approach
            });
            
            // For simplicity, we'll just clear all files when any is removed
            // In a production app, you'd want to implement a more precise removal
            galleryInput.value = '';
        }
    </script>

    <style>
        /* Add some styling for the drag-and-drop area */
        .dragover {
            border: 2px dashed #007bff;
            background-color: #f0f8ff;
        }
        
        /* Style for preview images */
        .gallery-preview-item {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        #imgpreview {
            position: relative;
        }
        
        #imgpreview .remove-image {
            top: 0;
            right: 0;
        }
        
        .flex-wrap {
            display: flex;
            flex-wrap: wrap;
        }
        
        .gap-10 {
            gap: 10px;
        }
    </style>
</x-admin-layout>