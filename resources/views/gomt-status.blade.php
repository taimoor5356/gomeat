
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

    <style>
        .center {
            margin-top: 40%;

            width: 100%;
            /* height: 20%; */

            border: 3px solid red;
            padding: 10px;
        }
    </style>

</head>
<!-- Body-->
<body >
<!-- Page Content-->
{{-- <div class="container pb-5 mb-2 mb-md-4">
   <p>{{$txn}}</p>
</div> --}}

<div class="input-group mb-3 center" >
    <input id="txn-hash" value="{{$txn}}" type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="button-addon2" readonly>
    <button class="btn btn-primary" type="button" onclick="copyToClipboard()" id="button-addon2">Click to Copy</button>
</div>


<script>
    function copyToClipboard() {
    var copyText = document.getElementById("txn-hash");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");

    alert('Transaction hash is copied, Please goto GoMeat app and paste the hash');
}
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>
