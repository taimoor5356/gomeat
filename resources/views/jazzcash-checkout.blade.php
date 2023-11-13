<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JazzCash CheckOut</title>
</head>
<body onload="closethisapp();">
    <form name="redirectpost" action="{{config('constants.jazzcash.TRANSACTION_POST_URL')}}" method="POST">
        @csrf
        @php
            $postData = Session::get('post_data');
        @endphp
        @foreach ($postData as $key => $value)
            <input type="hidden" name="{{$key}}" value="{{$value}}">
        @endforeach
    </form>
</body>
<script type="text/javascript">
    function closethisapp() {
        document.forms['redirectpost'].submit();
    }
</script>
</html>