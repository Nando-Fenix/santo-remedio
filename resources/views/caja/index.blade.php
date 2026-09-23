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

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-cash-stack"></i>
                Estado de caja
            </h2>

            <p>
                Sucursal actual:
                <strong>{{ $sucursal->nombre }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            @if (!$cajaAbierta && auth()->user()->tienePermiso('abrir_caja'))
                <a href="{{ route('caja.create') }}" class="btn-primary">
                    <i class="bi bi-unlock"></i>
                    Abrir caja
                </a>
            @endif

            @if ($cajaAbierta)
                @if (auth()->user()->tienePermiso('ver_caja'))
                    <a href="{{ route('caja.movimientos') }}" class="btn-secondary">
                        <i class="bi bi-list-ul"></i>
                        Movimientos
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('registrar_egreso'))
                    <a href="{{ route('caja.egreso.create') }}" class="btn-secondary">
                        <i class="bi bi-dash-circle"></i>
                        Egreso
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('cerrar_caja'))
                    <a href="{{ route('caja.cierre.create') }}" class="btn-primary">
                        <i class="bi bi-lock"></i>
                        Cerrar caja
                    </a>
                @endif
            @endif
        </div>
    </div>

    @if ($cajaAbierta)
        <div class="detail-stat-grid compact-detail-stats">
            <div class="detail-stat-card detail-stat-main">
                <span>Estado</span>
                <strong>Abierta</strong>
            </div>

            <div class="detail-stat-card">
                <span>Turno</span>
                <strong>{{ $cajaAbierta->turno->nombre ?? '-' }}</strong>
            </div>

            <div class="detail-stat-card">
                <span>Monto inicial</span>
                <strong>{{ number_format($cajaAbierta->monto_inicial, 2) }} Bs</strong>
            </div>

            <div class="detail-stat-card">
                <span>Apertura</span>
                <strong>{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</strong>
            </div>
        </div>

        <div class="cash-current-grid">
            <div class="cash-current-item">
                <span>Efectivo</span>
                <strong>{{ number_format($cajaAbierta->total_efectivo, 2) }} Bs</strong>
            </div>

            <div class="cash-current-item">
                <span>QR / Transferencia</span>
                <strong>{{ number_format($cajaAbierta->total_qr, 2) }} Bs</strong>
            </div>

            <div class="cash-current-item cash-current-danger">
                <span>Egresos</span>
                <strong>{{ number_format($cajaAbierta->total_egresos, 2) }} Bs</strong>
            </div>

            <div class="cash-current-item cash-current-danger">
                <span>Reembolsos / devoluciones</span>
                <strong>{{ number_format($cajaAbierta->total_reembolsos, 2) }} Bs</strong>
            </div>

            <div class="cash-current-item cash-current-total">
                <span>Total final estimado</span>
                <strong>{{ number_format($cajaAbierta->total_final, 2) }} Bs</strong>
            </div>
        </div>
    @else
        <div class="cash-empty-state">
            <div>
                <i class="bi bi-cash-stack"></i>
            </div>

            <section>
                <h3>No hay caja abierta</h3>
                <p>
                    Para registrar ventas reales con control de caja, primero debe abrir una caja.
                </p>
            </section>
        </div>
    @endif

</div>

<div class="compact-card" style="margin-top: 14px;">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-clock-history"></i>
                Historial de cajas
            </h2>

            <p>
                Aperturas y cierres registrados en el sistema.
            </p>
        </div>
    </div>

    <div class="table-container compact-table-container">
        <table class="table compact-table cash-history-table">
            <thead>
                <tr>
                    <th>Apertura</th>
                    <th>Cierre</th>
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
                        <td>
                            {{ $caja->fecha_apertura?->format('d/m/Y') }}
                            <br>
                            <small style="color:#6B7280;">
                                {{ $caja->fecha_apertura?->format('H:i') }}
                            </small>
                        </td>

                        <td>
                            @if ($caja->fecha_cierre)
                                {{ $caja->fecha_cierre->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $caja->fecha_cierre->format('H:i') }}
                                </small>
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $caja->usuario->nombre ?? '-' }}</td>
                        <td>{{ $caja->turno->nombre ?? '-' }}</td>
                        <td>{{ number_format($caja->monto_inicial, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_efectivo, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_qr, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_egresos, 2) }} Bs</td>

                        <td>
                            <strong>{{ number_format($caja->total_final, 2) }} Bs</strong>
                        </td>

                        <td>
                            <span class="badge {{ $caja->estado === 'abierta' ? 'badge-success' : 'badge-soft' }}">
                                {{ ucfirst($caja->estado) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="empty-table-message">
                            No existen cajas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $cajas->links() }}
    </div>

</div>

@endsection