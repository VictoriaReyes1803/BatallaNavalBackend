<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Account Activation</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700&display=swap" rel="stylesheet">
    <style>
        body, h2, p, a {
            margin: 0;
            padding: 0;
            font-family: 'Open Sans', sans-serif;
        }


        body {
            background-color: #e6f2ff;
            color: #333;
            line-height: 1.6;
        }


        .container {
            width: 80%;
            margin: 50px auto;
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }


        h2 {
            color: #00264d;
            padding: 20px 0;
        }


        p {
            color: #333;
        }


        a {
            display: inline-block;
            color: #fff;
            background-color: #004080;
            padding: 10px 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #0059b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome!</h2>
    <p>Hello {{$user['nombre']}},</p>
    <p>Please click the link below to verify your email and activate your account.</p>
    <a href="{{$url}}">Verify Email</a>
</div>
</body>
</html>
