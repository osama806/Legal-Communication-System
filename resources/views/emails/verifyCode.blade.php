<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify Code</title>
</head>

<body>
    <h1>Dear {{ $email }},</h1>
    <p>Using this code to confirm your email</p>
    <h3 style="text-align: center">{{ $code }}</h3>
    <span>Don't share this code with anyone</span>
</body>

</html>
