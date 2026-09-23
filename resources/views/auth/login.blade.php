<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión | Santo Remedio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --login-primary: #5b3f8c;
            --login-primary-dark: #3f2a66;
            --login-primary-soft: #ebe4f3;
            --login-green: #16a34a;
            --login-green-soft: #dcfce7;
            --login-bg: #f7f5fb;
            --login-text: #2d2340;
            --login-muted: #6f6682;
            --login-border: #ded7ea;
            --login-white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(22, 163, 74, 0.18), transparent 32%),
                radial-gradient(circle at bottom right, rgba(91, 63, 140, 0.25), transparent 34%),
                linear-gradient(135deg, #f8f5ff 0%, #eef8f0 48%, #f7f5fb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
            color: var(--login-text);
        }

        .login-shell {
            width: 100%;
            max-width: 980px;
            min-height: 560px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 26px 80px rgba(63, 42, 102, 0.20);
            backdrop-filter: blur(18px);
        }

        .login-info {
            position: relative;
            padding: 46px;
            background:
                linear-gradient(145deg, rgba(91, 63, 140, 0.96), rgba(63, 42, 102, 0.98)),
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.22), transparent 28%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .login-info::before,
        .login-info::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255,255,255,0.10);
        }

        .login-info::before {
            width: 230px;
            height: 230px;
            right: -80px;
            top: -70px;
        }

        .login-info::after {
            width: 180px;
            height: 180px;
            left: -70px;
            bottom: -60px;
        }

        .brand-area,
        .login-benefits,
        .login-footer-info {
            position: relative;
            z-index: 1;
        }

        .brand-mark {
            width: 68px;
            height: 68px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.24);
            margin-bottom: 22px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.10);
        }

        .brand-mark i {
            font-size: 34px;
        }

        .brand-area h1 {
            font-size: 35px;
            line-height: 1.05;
            margin: 0;
            letter-spacing: -0.8px;
        }

        .brand-area p {
            max-width: 390px;
            margin: 14px 0 0;
            color: rgba(255,255,255,0.78);
            line-height: 1.55;
            font-size: 15px;
        }

        .login-benefits {
            display: grid;
            gap: 12px;
            margin-top: 34px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            border-radius: 18px;
            background: rgba(255,255,255,0.11);
            border: 1px solid rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.92);
            font-size: 14px;
        }

        .benefit-item i {
            color: #bbf7d0;
            font-size: 18px;
        }

        .login-footer-info {
            margin-top: 30px;
            font-size: 13px;
            color: rgba(255,255,255,0.68);
        }

        .login-panel {
            padding: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(180deg, rgba(255,255,255,0.96), rgba(255,255,255,0.86));
        }

        .login-card {
            width: 100%;
            max-width: 390px;
        }

        .mobile-brand {
            display: none;
            text-align: center;
            margin-bottom: 24px;
        }

        .mobile-brand .brand-mark {
            margin: 0 auto 14px;
            background: var(--login-primary-soft);
            color: var(--login-primary);
            border-color: var(--login-border);
        }

        .login-title {
            margin-bottom: 26px;
        }

        .login-title span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border-radius: 999px;
            background: var(--login-green-soft);
            color: #166534;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .login-title h2 {
            margin: 0;
            font-size: 27px;
            color: var(--login-primary-dark);
            letter-spacing: -0.5px;
        }

        .login-title p {
            margin: 8px 0 0;
            color: var(--login-muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 16px;
            padding: 13px 14px;
            margin-bottom: 18px;
            font-size: 14px;
            line-height: 1.4;
        }

        .error i {
            font-size: 17px;
            margin-top: 1px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        label {
            display: block;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--login-text);
            font-size: 13px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--login-muted);
            font-size: 17px;
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 14px 14px 43px;
            border: 1px solid var(--login-border);
            border-radius: 15px;
            font-size: 15px;
            outline: none;
            background: #fff;
            color: var(--login-text);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        input::placeholder {
            color: #a09aad;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--login-primary);
            box-shadow: 0 0 0 4px rgba(91, 63, 140, 0.13);
            transform: translateY(-1px);
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 4px 0 20px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--login-muted);
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--login-primary);
        }

        .btn-login {
            width: 100%;
            padding: 15px 16px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--login-primary), var(--login-primary-dark));
            color: #ffffff;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            box-shadow: 0 14px 28px rgba(91, 63, 140, 0.28);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(91, 63, 140, 0.34);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-note {
            margin-top: 18px;
            padding: 13px 14px;
            border-radius: 16px;
            background: #faf8ff;
            border: 1px solid var(--login-border);
            color: var(--login-muted);
            font-size: 12.5px;
            line-height: 1.45;
            display: flex;
            gap: 9px;
        }

        .login-note i {
            color: var(--login-primary);
            font-size: 16px;
            margin-top: 1px;
        }

        @media (max-width: 860px) {
            .login-shell {
                grid-template-columns: 1fr;
                max-width: 470px;
                min-height: auto;
            }

            .login-info {
                display: none;
            }

            .mobile-brand {
                display: block;
            }

            .login-panel {
                padding: 34px 24px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
                align-items: flex-start;
            }

            .login-shell {
                border-radius: 24px;
            }

            .login-panel {
                padding: 28px 20px;
            }

            .login-title h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <main class="login-shell">

        <section class="login-info">
            <div class="brand-area">
                <div class="brand-mark">
                    <i class="bi bi-capsule-pill"></i>
                </div>

                <h1>Santo Remedio</h1>
                <p>
                    Sistema de gestión farmacéutica para ventas, caja, inventario,
                    compras, servicios y reportes.
                </p>
            </div>

            <div class="login-benefits">
                <div class="benefit-item">
                    <i class="bi bi-speedometer2"></i>
                    <span>Venta rápida y control de caja diario.</span>
                </div>

                <div class="benefit-item">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventario actualizado por sucursal.</span>
                </div>

                <div class="benefit-item">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Reportes claros para la administración.</span>
                </div>
            </div>

            <div class="login-footer-info">
                Acceso exclusivo para personal autorizado.
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">

                <div class="mobile-brand">
                    <div class="brand-mark">
                        <i class="bi bi-capsule-pill"></i>
                    </div>
                    <strong>Santo Remedio</strong>
                </div>

                <div class="login-title">
                    <span>
                        <i class="bi bi-shield-check"></i>
                        Acceso seguro
                    </span>

                    <h2>Iniciar sesión</h2>
                    <p>Ingrese sus credenciales para acceder al sistema.</p>
                </div>

                @if ($errors->any())
                    <div class="error">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="form-group">
                        <label for="usuario">Usuario</label>
                        <div class="input-wrap">
                            <i class="bi bi-person"></i>
                            <input
                                type="text"
                                id="usuario"
                                name="usuario"
                                value="{{ old('usuario') }}"
                                placeholder="Ingrese su usuario"
                                autocomplete="username"
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrap">
                            <i class="bi bi-lock"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Ingrese su contraseña"
                                autocomplete="current-password"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="remember">
                            <input type="checkbox" name="remember">
                            Recordar sesión
                        </label>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Ingresar al sistema
                    </button>
                </form>

                <div class="login-note">
                    <i class="bi bi-info-circle"></i>
                    <span>
                        Use el usuario asignado por administración. Los permisos se aplican según su cuenta.
                    </span>
                </div>
            </div>
        </section>

    </main>
</body>
</html>