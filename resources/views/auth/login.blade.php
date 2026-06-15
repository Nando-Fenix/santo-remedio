<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Santo Remedio | Iniciar sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4C1D95, #6D28D9, #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 22px;
            padding: 34px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
        }

        .brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand h1 {
            color: #4C1D95;
            margin: 0;
            font-size: 30px;
        }

        .brand p {
            color: #6B7280;
            margin-top: 8px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 7px;
            color: #374151;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #D1D5DB;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #6D28D9;
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.15);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #6D28D9;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #4C1D95;
        }

        .error {
            background: #FEE2E2;
            color: #991B1B;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            color: #4B5563;
        }

        .remember input {
            width: auto;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">
        <h1>Santo Remedio</h1>
        <p>Sistema de Gestión Farmacéutica</p>
    </div>

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="form-group">
            <label for="usuario">Usuario</label>
            <input 
                type="text" 
                id="usuario" 
                name="usuario" 
                value="{{ old('usuario') }}" 
                placeholder="Ingrese su usuario"
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Ingrese su contraseña"
            >
        </div>

        <label class="remember">
            <input type="checkbox" name="remember">
            Recordar sesión
        </label>

        <button type="submit" class="btn-login">
            Ingresar
        </button>
    </form>
</div>

</body>
</html>