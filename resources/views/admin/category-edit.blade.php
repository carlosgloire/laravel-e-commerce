<x-admin-layout title="Edit a category">
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Category information</h3>
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
                            <div class="text-tiny">categories</div>
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
                <form class="form-new-product form-style-1" action="{{route('admin.category.update')}}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{$category->id}}">
                    <fieldset class="name">
                        <div class="body-title">Category Name <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="text" placeholder="category name" name="name"
                            id="categoryName" tabindex="0" value="{{$category->name}}" aria-required="true" required="">
                    </fieldset>
                    @error('name') <span class="alert alert-danger text-center">{{$message}}</span>@enderror
                    <fieldset class="name">
                        <div class="body-title">Category Slug <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="text" placeholder="category Slug" name="slug"
                            id="categorySlug" tabindex="0" value="{{$category->slug}}" aria-required="true" required="">
                    </fieldset>
                    @error('slug') <span class="alert alert-danger text-center">{{$message}}</span>@enderror
                    <fieldset>
                        <div class="body-title">Upload images <span class="tf-color-1">*</span>
                        </div>
                        <div class="upload-image flex-grow">
                            @if ($category->image)
                                <div class="item" id="imgpreview" >
                                    <img src="/uploads/categories/{{$category->image}}" class="effect8" alt="" id="previewImage">
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
            const categoryNameInput = document.getElementById('categoryName');
            const categorySlugInput = document.getElementById('categorySlug');
            const imageInput = document.getElementById('myFile');
            const previewImage = document.getElementById('previewImage');
            const imgPreviewDiv = document.getElementById('imgpreview');
            const uploadFileDiv = document.getElementById('upload-file');

            // Generate slug from category name
            categoryNameInput.addEventListener('input', function() {
                const name = categoryNameInput.value.trim();
                const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                categorySlugInput.value = slug;
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
                    previewImage.src = e.target.result;
                    imgPreviewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <style>
        /* Add some styling for the drag-and-drop area */
        .dragover {
            border: 2px dashed #007bff;
            background-color: #f0f8ff;
        }
    </style>
</x-admin-layout>