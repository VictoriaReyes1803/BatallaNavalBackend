<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One-time verification code</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #000;
            color: #ff0000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200vh;
        }
        .container {
            text-align: center;
            background: #000;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 20px rgba(255,0,0,0.2);
        }
        h1, h2, p {
            color: #ff0000; 
            margin-bottom: 20px;
            font-weight: bold;
        }
        h1 {
            font-size: 48px; 
        }
        h2 {
            font-size: 36px;
        }
        p {
            font-size: 18px;
        }
        a {
            display: inline-block;
            color: #ff0000;
            background-color: #000;
            padding: 15px 30px;
            margin: 20px 0;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .code {
            font-size: 36px;
            letter-spacing: 8px;
        }

        .logo {
            height: 30vh;
            width: 30vh;
            background-size: 150%;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Batalla Naval</h2>
    <p>Por favor verifica el código:</p>
    <a><h1 class="code">{{$code}}</h1></a>
</div>
</body>
</html>
