@extends('layouts.app')

@section('title', 'Caja | Santo Remedio')
@section('page-title', 'Caja')
@section('page-subtitle', 'Control de apertura, movimientos y cierre de caja')

@section('content')

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Estado de caja</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Sucursal actual: <strong>{{ $sucursal->nombre }}</strong>
            </p>
        </div>

        @if (!$cajaAbierta)
            <a href="{{ route('caja.create') }}" class="btn-primary">
                Abrir caja
            </a>
        @endif

        @if ($cajaAbierta)
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 22px;">
                <a href="{{ route('caja.movimientos') }}" class="btn-secondary">
                    Ver movimientos
                </a>

                <a href="{{ route('caja.egreso.create') }}" class="btn-secondary">
                    Registrar egreso
                </a>

                <a href="{{ route('caja.cierre.create') }}" class="btn-primary">
                    Cerrar caja
                </a>
            </div>
        @endif
    </div>
</div>

@if ($cajaAbierta)
    <div class="grid">
        <div class="stat-card">
            <span>Estado</span>
            <h3>Abierta</h3>
        </div>

        <div class="stat-card">
            <span>Turno</span>
            <h3 style="font-size: 20px;">{{ $cajaAbierta->turno->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Monto inicial</span>
            <h3>{{ number_format($cajaAbierta->monto_inicial, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Apertura</span>
            <h3 style="font-size: 18px;">{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</h3>
        </div>
    </div>

    <div class="card" style="margin-bottom: 22px;">
        <h3 style="margin-top: 0; color: #4C1D95;">Resumen actual</h3>

        <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 0;">
            <div class="stat-card">
                <span>Efectivo</span>
                <h3>{{ number_format($cajaAbierta->total_efectivo, 2) }} Bs</h3>
            </div>

            <div class="stat-card">
                <span>QR / Transferencia</span>
                <h3>{{ number_format($cajaAbierta->total_qr, 2) }} Bs</h3>
            </div>

            <div class="stat-card">
                <span>Egresos</span>
                <h3>{{ number_format($cajaAbierta->total_egresos, 2) }} Bs</h3>
            </div>

            <div class="stat-card">
                <span>Total final estimado</span>
                <h3>{{ number_format($cajaAbierta->total_final, 2) }} Bs</h3>
            </div>
        </div>
    </div>
@else
    <div class="card" style="margin-bottom: 22px;">
        <h3 style="margin-top: 0; color: #4C1D95;">No hay caja abierta</h3>
        <p style="color: #6B7280;">
            Para registrar ventas reales con control de caja, primero debe abrir una caja.
        </p>
    </div>
@endif

<div class="card">
    <h3 style="margin-top: 0; color: #4C1D95;">Historial de cajas</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha apertura</th>
                    <th>Fecha cierre</th>
                    <th>Usuario</th>
                    <th>Turno</th>
                    <th>Inicial</th>
                    <th>Efectivo</th>
                    <th>QR</th>
                    <th>Egresos</th>
                    <th>Final</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cajas as $caja)
                    <tr>
                        <td>{{ $caja->fecha_apertura?->format('d/m/Y H:i') }}</td>
                        <td>{{ $caja->fecha_cierre?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $caja->usuario->nombre ?? '-' }}</td>
                        <td>{{ $caja->turno->nombre ?? '-' }}</td>
                        <td>{{ number_format($caja->monto_inicial, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_efectivo, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_qr, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_egresos, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_final, 2) }} Bs</td>
                        <td>
                            <span class="badge {{ $caja->estado === 'abierta' ? 'badge-success' : 'badge-soft' }}">
                                {{ ucfirst($caja->estado) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: #6B7280;">
                            No existen cajas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $cajas->links() }}
    </div>
</div>

@endsection