@props(['title'=>''])
<!DOCTYPE html>
<html lang="{{str_replace('_','-',app()->getLocale())}}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>{{$title}} - {{config("app.name", 'Laravel')}}</title>

  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="author" content="surfside media" />
  <link rel="shortcut icon" href="/images/logo/logo.png" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.gstatic.com/">
  <link
    href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Allura&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/plugins/swiper.min.css" type="text/css" />
  <link rel="stylesheet" href="/assets/css/style.css" type="text/css" />
  <link rel="stylesheet" href="/assets/css/custom.css" type="text/css" />
  <link rel="stylesheet" type="text/css" href="/css/sweetalert.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw=="
    crossorigin="anonymous" referrerpolicy="no-referrer">
  </head>
  
  <body class="gradient-bg">

    {{$slot}}

    <div id="scrollTop" class="visually-hidden end-0"></div>
    <div class="page-overlay"></div>

    <script src="/js/sweetalert.min.js"></script>    
    <script src="/assets/js/plugins/jquery.min.js"></script>
    <script src="/assets/js/plugins/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/plugins/bootstrap-slider.min.js"></script>
    <script src="/assets/js/plugins/swiper.min.js"></script>
    <script src="/assets/js/plugins/countdown.js"></script>
    <script src="/assets/js/theme.js"></script>
 
     <script>
    $(document).ready(function() {
    const searchInput = $('#search-input');
    
    if (searchInput.length) {
        searchInput.on('keyup', function() {
            const searchQuery = $(this).val().trim();
            
            if (searchQuery.length > 2) {
                const url = "{{ route('home.search') }}";
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: { query: searchQuery },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        console.log('Searching for:', searchQuery);
                    },
                    success: function(data) {
                        console.log('Search results:', data);
                        const boxContentSearch = $('#box-content-search');
                        boxContentSearch.empty();
                        
                        if (!data || data.length === 0) {
                            boxContentSearch.append('<li class="p-3">No products found</li>');
                            return;
                        }
                        
                        $.each(data, function(index, item) {
                            let productUrl = "{{ route('shop.product.detail',['product_slug'=>'product_slug_pls']) }}";
                            productUrl = productUrl.replace('product_slug_pls', item.slug);
                            
                            // Create the product item HTML
                            const productItem = `
                                <ul>
                                    <li class="product-item gap14 mb-10">
                                        <div class="image no-bg">
                                            <img src="/uploads/products/${item.image}" alt="${item.name}">
                                        </div>
                                        <div class="flex items-center justify-between gap20 flex-grow">
                                            <div class="name">
                                                <a href="${productUrl}" class="body-text">${item.name}</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-10">
                                        <div class="divider"></div>
                                    </li>
                                </ul>
                            `;
                            
                            boxContentSearch.append(productItem);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Search error:', error);
                        $('#box-content-search').html('<li class="p-3 text-danger">Error loading search results</li>');
                    }
                });
            } else {
                // Clear results if query is too short
                $('#box-content-search').empty();
            }
        });
    }
});
</script>


  </body>
</html>