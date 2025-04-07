<x-admin-layout title="Add a brand">
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Category infomation</h3>
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
                        <a href="{{route('admin.categories')}}">
                            <div class="text-tiny">Categories</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">New Category</div>
                    </li>
                </ul>
            </div>
            <!-- new-category -->
            <div class="wg-box">
                <form class="form-new-product form-style-1" action="{{route('admin.category.store')}}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @if (Session::has('success'))
                            <p class="alert alert-success">{{Session::get('success')}}</p>
                    @endif
                    <fieldset class="name">
                        <div class="body-title">Category Name <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="text" placeholder="Category name" name="name"
                            id="brandName" tabindex="0" value="{{old('name')}}" aria-required="true" required="">
                    </fieldset>
                    @error('name') <span class="alert alert-danger text-center">{{$message}}</span>@enderror
                    <fieldset class="name">
                        <div class="body-title">Category Slug <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="text" placeholder="Category Slug" name="slug"
                            id="brandSlug" tabindex="0" value="{{old('slug')}}" aria-required="true" required="">
                    </fieldset>
                    @error('slug') <span class="alert alert-danger text-center">{{$message}}</span>@enderror
                    <fieldset>
                        <div class="body-title">Upload images <span class="tf-color-1">*</span>
                        </div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="imgpreview" style="display:none">
                                <img src="" class="effect8 previewImage" alt="Preview">
                                <button type="button" class="remove-image" onclick="removeImage('imgpreview', 'myFile')">×</button>
                            </div>
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
                    @error('image') <span class="alert alert-danger text-center">{{$message}}</span>@enderror
                    <div class="bot">
                        <div></div>
                        <button class="tf-button w208" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const brandNameInput = document.getElementById('brandName');
            const brandSlugInput = document.getElementById('brandSlug');
            const imageInput = document.getElementById('myFile');
            const imgPreviewDiv = document.getElementById('imgpreview');
            const uploadFileDiv = document.getElementById('upload-file');

            // Generate slug from brand name
            brandNameInput.addEventListener('input', function() {
                const name = brandNameInput.value.trim();
                const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                brandSlugInput.value = slug;
            });

            // Preview uploaded image (for file input)
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    previewImageFile(file);
                }
            });

            // Drag and drop functionality
            uploadFileDiv.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadFileDiv.classList.add('dragover');
            });

            uploadFileDiv.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadFileDiv.classList.remove('dragover');
            });

            uploadFileDiv.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadFileDiv.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    imageInput.files = e.dataTransfer.files; // Assign the file to the input
                    previewImageFile(file);
                } else {
                    alert('Please upload a valid image file.');
                }
            });

            // Function to preview the image
            function previewImageFile(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImage = imgPreviewDiv.querySelector('img');
                    previewImage.src = e.target.result;
                    imgPreviewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Remove image
        function removeImage(previewId, inputId) {
            document.getElementById(previewId).style.display = 'none';
            document.getElementById(inputId).value = '';
        }
    </script>

    <style>
        /* Add some styling for the drag-and-drop area */
        .dragover {
            border: 2px dashed #007bff;
            background-color: #f0f8ff;
        }
        
        /* Style for preview images */
        #imgpreview {
            position: relative;
        }
        
        .remove-image {
            position: absolute;
            top: 0;
            right: 0;
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
        
        .previewImage {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
        }
    </style>
</x-admin-layout>