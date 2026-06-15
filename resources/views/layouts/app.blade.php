<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Santo Remedio')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>

<div class="app">

    <aside class="sidebar">
        <div class="brand">
            <h2>Santo Remedio</h2>
            <p>Sistema de Gestión Farmacéutica</p>
        </div>

        <nav class="menu">
            <a href="{{ route('dashboard') }}" class="active">Dashboard</a>
            <a href="{{ route('ventas.index') }}">Ventas</a>
            <a href="{{ route('productos.index') }}">Productos</a>
            <a href="{{ route('inventario.index') }}">Inventario</a>
            <a href="{{ route('caja.index') }}">Caja</a>
            <a href="{{ route('clientes.index') }}">Clientes</a>
            <a href="{{ route('reportes.index') }}">Reportes</a>
            <a href="{{ route('proveedores.index') }}">Proveedores</a>
            <a href="{{ route('compras.deudas') }}">Deudas proveedores</a>
            <a href="{{ route('compras.index') }}">Compras</a>
            <a href="#">Usuarios</a>
            <a href="#">Configuración</a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="topbar-title">
                <h1>@yield('page-title', 'Dashboard')</h1>
                <p>@yield('page-subtitle', 'Resumen general del sistema')</p>
            </div>

            <div class="user-box">
                <div class="user-info">
                    <strong>{{ auth()->user()->nombre }}</strong>
                    <span>{{ auth()->user()->rol->nombre ?? 'Sin rol' }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-logout" type="submit">
                        Salir
                    </button>
                </form>
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