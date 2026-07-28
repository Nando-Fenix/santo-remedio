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
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
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
                    <span class="menu-icon"><i class="bi bi-upc-scan"></i></span>
                    <span class="menu-text">Nueva venta</span>
                </a>
            @endif

            <details class="menu-group" {{ request()->routeIs('dashboard') ? 'open' : '' }}>
                <summary>
                    <span><i class="bi bi-house-door"></i> Inicio</span>
                    <span class="menu-arrow">⌄</span>
                </summary>

                @if ($user->tienePermiso('ver_dashboard'))
                    <a href="{{ route('dashboard') }}"
                    class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-speedometer2"></i></span>
                        <span class="menu-text">Panel principal</span>
                    </a>
                @endif
            </details>

            <details class="menu-group" {{ request()->routeIs('ventas.*') || request()->routeIs('caja.*') || request()->routeIs('atenciones-servicio.*') ? 'open' : '' }}>
                <summary>
                    <span><i class="bi bi-lightning-charge"></i> Operación</span>
                    <span class="menu-arrow">⌄</span>
                </summary>

                @if ($user->tienePermiso('ver_ventas'))
                    <a href="{{ route('ventas.index') }}"
                    class="menu-link {{ request()->routeIs('ventas.*') && !request()->routeIs('ventas.create') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-receipt"></i></span>
                        <span class="menu-text">Ventas realizadas</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_caja'))
                    <a href="{{ route('caja.index') }}"
                    class="menu-link {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-cash-coin"></i></span>
                        <span class="menu-text">Caja</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_atenciones_servicio'))
                    <a href="{{ route('atenciones-servicio.index') }}"
                    class="menu-link {{ request()->routeIs('atenciones-servicio.*') && !request()->routeIs('atenciones-servicio.create') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-clipboard2-pulse"></i></span>
                        <span class="menu-text">Atenciones de servicio</span>
                    </a>
                @endif

                @if ($user->tienePermiso('registrar_atencion_servicio'))
                    <a href="{{ route('atenciones-servicio.create') }}"
                    class="menu-link {{ request()->routeIs('atenciones-servicio.create') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-plus-circle"></i></span>
                        <span class="menu-text">Registrar atención</span>
                    </a>
                @endif
            </details>

            <details class="menu-group" {{ request()->routeIs('inventario.*') || request()->routeIs('productos.*') || request()->routeIs('compras.*') || request()->routeIs('bajas-inventario.*') || request()->routeIs('promociones.*') ? 'open' : '' }}>
                <summary>
                    <span><i class="bi bi-box-seam"></i> Inventario</span>
                    <span class="menu-arrow">⌄</span>
                </summary>

                @if ($user->tienePermiso('ver_inventario'))
                    <a href="{{ route('inventario.index') }}"
                    class="menu-link {{ request()->routeIs('inventario.index') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-boxes"></i></span>
                        <span class="menu-text">Inventario general</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_productos'))
                    <a href="{{ route('productos.index') }}"
                    class="menu-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-capsule"></i></span>
                        <span class="menu-text">Productos</span>
                    </a>
                @endif

                @if ($user->tienePermiso('registrar_compra'))
                    <a href="{{ route('compras.create') }}"
                    class="menu-link {{ request()->routeIs('compras.create') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-bag-plus"></i></span>
                        <span class="menu-text">Registrar compra / entrada</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_movimientos_inventario'))
                    <a href="{{ route('inventario.movimientos') }}"
                    class="menu-link {{ request()->routeIs('inventario.movimientos') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-arrow-left-right"></i></span>
                        <span class="menu-text">Movimientos</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_inventario'))
                    <a href="{{ route('inventario.proximos-vencer') }}"
                    class="menu-link {{ request()->routeIs('inventario.proximos-vencer') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-calendar2-x"></i></span>
                        <span class="menu-text">Próximos a vencer</span>
                    </a>

                    <a href="{{ route('inventario.productos-vencidos') }}"
                    class="menu-link {{ request()->routeIs('inventario.productos-vencidos') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-exclamation-triangle"></i></span>
                        <span class="menu-text">Productos vencidos</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_bajas_inventario'))
                    <a href="{{ route('bajas-inventario.index') }}"
                    class="menu-link {{ request()->routeIs('bajas-inventario.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-trash3"></i></span>
                        <span class="menu-text">Bajas de inventario</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_promociones'))
                    <a href="{{ route('promociones.index') }}"
                    class="menu-link {{ request()->routeIs('promociones.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-tags"></i></span>
                        <span class="menu-text">Promociones</span>
                    </a>
                @endif
            </details>

            <details class="menu-group" {{ request()->routeIs('clientes.*') || request()->routeIs('proveedores.*') || request()->routeIs('servicios-farmacia.*') || request()->routeIs('compras.deudas') || request()->routeIs('compras.index') || request()->routeIs('compras.show') ? 'open' : '' }}>
                <summary>
                    <span><i class="bi bi-folder2-open"></i> Gestión</span>
                    <span class="menu-arrow">⌄</span>
                </summary>

                @if ($user->tienePermiso('ver_clientes'))
                    <a href="{{ route('clientes.index') }}"
                    class="menu-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-people"></i></span>
                        <span class="menu-text">Clientes</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_proveedores'))
                    <a href="{{ route('proveedores.index') }}"
                    class="menu-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-truck"></i></span>
                        <span class="menu-text">Proveedores</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_compras'))
                    <a href="{{ route('compras.index') }}"
                    class="menu-link {{ request()->routeIs('compras.index') || request()->routeIs('compras.show') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-bag-check"></i></span>
                        <span class="menu-text">Compras realizadas</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_deudas_proveedores'))
                    <a href="{{ route('compras.deudas') }}"
                    class="menu-link {{ request()->routeIs('compras.deudas') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-file-earmark-text"></i></span>
                        <span class="menu-text">Deudas proveedores</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_servicios_farmacia'))
                    <a href="{{ route('servicios-farmacia.index') }}"
                    class="menu-link {{ request()->routeIs('servicios-farmacia.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-heart-pulse"></i></span>
                        <span class="menu-text">Servicios de farmacia</span>
                    </a>
                @endif
            </details>

            <details class="menu-group" {{ request()->routeIs('usuarios.*') || request()->routeIs('reportes.*') ? 'open' : '' }}>
                <summary>
                    <span><i class="bi bi-shield-lock"></i> Administración</span>
                    <span class="menu-arrow">⌄</span>
                </summary>

                @if ($user->tienePermiso('administrar_usuarios'))
                    <a href="{{ route('usuarios.index') }}"
                    class="menu-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-person-lock"></i></span>
                        <span class="menu-text">Usuarios</span>
                    </a>
                @endif

                @if ($user->tienePermiso('ver_reportes'))
                    <a href="{{ route('reportes.index') }}"
                    class="menu-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-bar-chart"></i></span>
                        <span class="menu-text">Reportes</span>
                    </a>
                @endif
            </details>

        </nav>

    </aside>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" title="Abrir menú">
                    <i class="bi bi-list"></i>
                </button>

                <div class="page-title-icon">
                    <i class="bi bi-house-door"></i>
                </div>

                <div class="topbar-title">
                    <h1>@yield('page-title', 'Panel principal')</h1>
                    <p>@yield('page-subtitle', 'Resumen general del sistema')</p>
                </div>
            </div>

            <div class="topbar-actions">
                @if ($user->tienePermiso('realizar_venta'))
                    <a href="{{ route('ventas.create') }}"
                    class="topbar-main-action"
                    title="Ir a venta rápida">
                        <i class="bi bi-upc-scan"></i>
                        <span>Nueva venta</span>
                    </a>
                @endif

                <div class="topbar-user">
                    <div class="user-avatar">
                        <i class="bi bi-person"></i>
                    </div>

                    <div class="user-info">
                        <strong>{{ $user->nombre }}</strong>
                        <span>{{ $user->rol->nombre ?? 'Sin rol' }}</span>
                    </div>
                </div>

                <div class="topbar-icon-actions">
                    @if ($user->tienePermiso('administrar_configuracion'))
                        <a href="{{ route('configuracion.edit') }}"
                        class="topbar-icon-btn {{ request()->routeIs('configuracion.*') ? 'active' : '' }}"
                        title="Configuración">
                            <i class="bi bi-gear"></i>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="topbar-icon-btn topbar-logout" type="submit" title="Salir">
                            <i class="bi bi-power"></i>
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

    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (mobileMenuBtn && sidebar && sidebarOverlay) {
            mobileMenuBtn.addEventListener('click', function () {
                sidebar.classList.add('sidebar-open');
                sidebarOverlay.classList.add('active');
            });

            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('sidebar-open');
                sidebarOverlay.classList.remove('active');
            });

            document.querySelectorAll('.sidebar .menu-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 900) {
                        sidebar.classList.remove('sidebar-open');
                        sidebarOverlay.classList.remove('active');
                    }
                });
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>