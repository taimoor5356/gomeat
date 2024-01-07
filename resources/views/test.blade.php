
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>
        @yield('title')
    </title>
    <!-- SEO Meta Tags-->
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <!-- Viewport-->
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon and Touch Icons-->
    <link rel="shortcut icon" href="favicon.ico">
    <!-- Font -->
    <!-- CSS Implementing Plugins -->
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
</head>
<!-- Body-->
<body class="toolbar-enabled">
<!-- Page Content-->
<div class="container pb-5 mb-2 mb-md-4">
   
</div>





<script type="text/javascript">
$(document).ready(async function() {

    const tokenAddress = '0xFB1172b050BcC798e37AE8abF620Cc528e771162';
    const tokenSymbol = 'GOMT';
    const tokenDecimals = 8;
    const tokenImage = 'https://gomeat.io/assets/img/photos/GOMT Icon.png';
    
    try {
      // wasAdded is a boolean. Like any RPC method, an error may be thrown.
      
            const wasAdded = await ethereum.request({
                method: 'wallet_watchAsset',
                params: {
                type: 'ERC20', // Initially only supports ERC20, but eventually more!
                options: {
                    address: tokenAddress, // The address that the token is at.
                    symbol: tokenSymbol, // A ticker symbol or shorthand, up to 5 chars.
                    decimals: tokenDecimals, // The number of decimals in the token
                    image: tokenImage, // A string url of the token logo
                },
                },
            });
            
            if (wasAdded) {
                console.log('Thanks for your interest!');
            } else {
                console.log('Your loss!');
            }

        } catch (error) {
        console.log(error);
        }
});

</script>
</body>
</html>
