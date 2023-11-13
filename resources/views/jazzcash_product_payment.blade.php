<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Payment</title>
</head>
<body>
    <form action="{{route('jazzcash.checkout')}}" method="POST" id="myCCForm">
        @csrf
        <input type="text" name="product_id" value="1">
        <br>
        <input type="text" name="fullname" value="Ahmed">
        <br>
        <input type="text" name="email" value="email@email.com">
        <br>
        <input type="text" name="address" value="user address">
        <br>
        <input type="text" name="country" value="Pakistan">
        <br>
        <input type="text" name="city" value="Lahore">
        <br>
        <input type="text" name="state" value="punjab">
        <br>
        <input type="text" name="zip_code" value="54000">
        <br>
        <input type="submit">
    </form>
</body>
</html>