<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .code { font-size: 24px; font-weight: bold; color: #2d8cf0; text-align: center; margin: 20px 0; }
        .button { display: inline-block; background: #2d8cf0; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
        .footer { margin-top: 20px; font-size: 12px; color: #999; text-align: center; }
    </style>
    <title></title>
</head>
<body>
<div class="container">
    <h1>Contraseña temporal</h1>
    <p>Tu nueva contraseña temporal es:</p>
    <div class="code">{{ $tempPassword }}</div>
    <p>Por favor, inicia sesión y cámbiala cuanto antes.</p>
    <div style="text-align: center;">
        <a href="{{ route('login') }}" class="button">Iniciar sesión</a>
    </div>
    <div class="footer">
        Gracias,<br>
        {{ config('app.name') }}
    </div>
</div>
</body>
</html>
