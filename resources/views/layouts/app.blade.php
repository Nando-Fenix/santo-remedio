<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Santo Remedio')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

@php
    $user = auth()->user();
@endphp

<div class="app">

    <aside class="sidebar">
        <div class="brand">
            <h2>Santo Remedio</h2>
            <p>Gestión Farmacéutica</p>
        </div>

        <nav class="menu">

            @if ($user->tienePermiso('realizar_venta'))
                <a href="{{ route('ventas.create') }}"
                   class="menu-link menu-link-primary {{ request()->routeIs('ventas.create') ? 'active' : '' }}"
                   title="Venta rápida">
                    <span class="menu-icon">🛒</span>
                    <span class="menu-text">Venta rápida</span>
                </a>
            @endif

            <div class="menu-section">Principal</div>

            @if ($user->tienePermiso('ver_dashboard'))
                <a href="{{ route('dashboard') }}"
                   class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   title="Dashboard">
                    <span class="menu-icon">📊</span>
                    <span class="menu-text">Dashboard</span>
                </a>
            @endif

            @if ($user->tienePermiso('ver_ventas'))
                <a href="{{ route('ventas.index') }}"
                   class="menu-link {{ request()->routeIs('ventas.*') && !request()->routeIs('ventas.create') ? 'active' : '' }}"
                   title="Ventas">
                    <span class="menu-icon">🧾</span>
                    <span class="menu-text">Ventas</span>
                </a>
            @endif

            <div class="menu-section">Operación</div>

            @if ($user->tienePermiso('ver_caja'))
                <a href="{{ route('caja.index') }}"
                   class="menu-link {{ request()->routeIs('caja.*') ? 'active' : '' }}"
                   title="Caja">
                    <span class="menu-icon">💵</span>
                    <span class="menu-text">Caja</span>
                </a>
            @endif

            @if ($user->tienePermiso('ver_clientes'))
                <a href="{{ route('clientes.index') }}"
                   class="menu-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
                   title="Clientes">
                    <span class="menu-icon">👥</span>
                    <span class="menu-text">Clientes</span>
                </a>
            @endif

            @if ($user->tienePermiso('ver_inventario'))
                <a href="{{ route('inventario.index') }}"
                   class="menu-link {{ request()->routeIs('inventario.*') ? 'active' : '' }}"
                   title="Inventario">
                    <span class="menu-icon">📦</span>
                    <span class="menu-text">Inventario</span>
                </a>
            @endif

            <div class="menu-section">Administración</div>

            @if ($user->tienePermiso('ver_productos'))
                <a href="{{ route('productos.index') }}"
                   class="menu-link {{ request()->routeIs('productos.*') ? 'active' : '' }}"
                   title="Productos">
                    <span class="menu-icon">💊</span>
                    <span class="menu-text">Productos</span>
                </a>
            @endif

            @if ($user->tienePermiso('ver_compras'))
                <a href="{{ route('compras.index') }}"
                   class="menu-link {{ request()->routeIs('compras.index') || request()->routeIs('compras.show') || request()->routeIs('compras.create') ? 'active' : '' }}"
                   title="Compras">
                    <span class="menu-icon">🛍️</span>
                    <span class="menu-text">Compras</span>
                </a>
            @endif

            @if ($user->tienePermiso('ver_deudas_proveedores'))
                <a href="{{ route('compras.deudas') }}"
                   class="menu-link {{ request()->routeIs('compras.deudas') ? 'active' : '' }}"
                   title="Deudas proveedores">
                    <span class="menu-icon">📄</span>
                    <span class="menu-text">Deudas</span>
                </a>
            @endif

            @if ($user->tienePermiso('ver_proveedores'))
                <a href="{{ route('proveedores.index') }}"
                   class="menu-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}"
                   title="Proveedores">
                    <span class="menu-icon">🚚</span>
                    <span class="menu-text">Proveedores</span>
                </a>
            @endif

            @if ($user->tienePermiso('administrar_usuarios'))
                <a href="{{ route('usuarios.index') }}"
                   class="menu-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"
                   title="Usuarios">
                    <span class="menu-icon">👤</span>
                    <span class="menu-text">Usuarios</span>
                </a>
            @endif

            <div class="menu-section">Control</div>

            @if ($user->tienePermiso('ver_reportes'))
                <a href="{{ route('reportes.index') }}"
                   class="menu-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
                   title="Reportes">
                    <span class="menu-icon">📈</span>
                    <span class="menu-text">Reportes</span>
                </a>
            @endif

        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="topbar-title">
                <h1>@yield('page-title', 'Dashboard')</h1>
                <p>@yield('page-subtitle', 'Resumen general del sistema')</p>
            </div>

            <div class="topbar-actions">
                @if ($user->tienePermiso('realizar_venta'))
                    <a href="{{ route('ventas.create') }}" class="btn-quick-sale" title="Ir a venta rápida">
                        🛒 Venta rápida
                    </a>
                @endif

                <div class="user-box">
                    <div class="user-info">
                        <strong>{{ $user->nombre }}</strong>
                        <span>{{ $user->rol->nombre ?? 'Sin rol' }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-logout" type="submit">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <section class="content">
            @yield('content')
        </section>
    </main>

</div>

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Correcto',
                text: @json(session('success')),
                confirmButtonColor: '#6D28D9'
            });
        });
    </script>
@endif

@if (session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: @json(session('info')),
                confirmButtonColor: '#6D28D9'
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: @json(session('error')),
                confirmButtonColor: '#6D28D9'
            });
        });
    </script>
@endif

<script>
    function confirmarFormulario(event, mensaje = '¿Está seguro de continuar?') {
        event.preventDefault();

        const form = event.target;

        Swal.fire({
            title: mensaje,
            text: 'Esta acción quedará registrada en el sistema.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6D28D9',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }
</script>

</body>
</html>