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
        <input type="text" name="fullname" value="Ahmed">
        <input type="text" name="email" value="email@email.com">
        <input type="text" name="address" value="user address">
        <input type="text" name="country" value="Pakistan">
        <input type="text" name="city" value="Lahore">
        <input type="text" name="state" value="punjab">
        <input type="text" name="zip_code" value="54000">
        <input type="submit">
    </form>
</body>
</html>