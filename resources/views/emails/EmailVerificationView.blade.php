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
            background-color: #000;
            color: #ff0000;
            line-height: 1.6;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            text-align: center;
            background-color: #000;
            color: #ff0000;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 0px 10px rgba(255,0,0,0.1);
        }

        h2 {
            color: #ff0000;
            padding: 20px 0;
        }

        p {
            color: #ff0000;
        }

        a {
            display: inline-block;
            color: #ff0000;
            background-color: #000;
            padding: 10px 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #ff0000;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Éxito!</h2>
    <p>Hola {{$user['nombre']}},</p>
    <p>Por favor haz clic <a href="{{$url}}" style="color: #ff0000;">aquí</a> para verificar tu cuenta :D.</p>
    <a href="{{$url}}">Verificar</a>
</div>
</body>
</html>
