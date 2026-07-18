@extends('layouts.app')

@section('title', 'Bajas de inventario | Santo Remedio')
@section('page-title', 'Bajas de inventario')
@section('page-subtitle', 'Productos retirados por vencimiento, daño, pérdida u otros motivos')

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

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Bajas de inventario</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Historial de productos retirados del inventario.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('registrar_baja_inventario'))
            <a href="{{ route('bajas-inventario.create') }}" class="btn-primary">
                + Registrar baja
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('bajas-inventario.index') }}" style="margin-bottom: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Nombre comercial, genérico o concentración"
                >
            </div>

            <div class="form-group">
                <label>Motivo</label>
                <select name="motivo">
                    <option value="">Todos</option>
                    <option value="vencimiento" @selected(($motivo ?? '') === 'vencimiento')>Vencimiento</option>
                    <option value="danado" @selected(($motivo ?? '') === 'danado')>Dañado</option>
                    <option value="perdido" @selected(($motivo ?? '') === 'perdido')>Perdido</option>
                    <option value="ajuste_autorizado" @selected(($motivo ?? '') === 'ajuste_autorizado')>Ajuste autorizado</option>
                    <option value="otro" @selected(($motivo ?? '') === 'otro')>Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="registrado" @selected(($estado ?? '') === 'registrado')>Registrado</option>
                    <option value="anulado" @selected(($estado ?? '') === 'anulado')>Anulado</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Sucursal</th>
                    <th>Motivo</th>
                    <th>Cantidad</th>
                    <th>Stock anterior</th>
                    <th>Stock nuevo</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($bajas as $baja)
                    <tr>
                        <td>
                            <strong>{{ $baja->producto->nombre_comercial ?? '-' }}</strong>

                            @if ($baja->producto?->laboratorio || $baja->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">
                                    @if ($baja->producto?->laboratorio)
                                        {{ $baja->producto->laboratorio->nombre }}
                                    @endif

                                    @if ($baja->producto?->laboratorio && $baja->producto?->concentracion)
                                        |
                                    @endif

                                    @if ($baja->producto?->concentracion)
                                        {{ $baja->producto->concentracion }}
                                    @endif
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ $baja->lote->numero_lote ?? 'Sin lote' }}

                            @if ($baja->lote?->fecha_vencimiento)
                                <br>
                                <small style="color: #6B7280;">
                                    Vence: {{ $baja->lote->fecha_vencimiento->format('d/m/Y') }}
                                </small>
                            @endif
                        </td>

                        <td>{{ $baja->sucursal->nombre ?? '-' }}</td>

                        <td>
                            @if ($baja->motivo === 'vencimiento')
                                Vencimiento
                            @elseif ($baja->motivo === 'danado')
                                Dañado
                            @elseif ($baja->motivo === 'perdido')
                                Perdido
                            @elseif ($baja->motivo === 'ajuste_autorizado')
                                Ajuste autorizado
                            @else
                                Otro
                            @endif
                        </td>

                        <td>{{ $baja->cantidad }}</td>
                        <td>{{ $baja->stock_anterior }}</td>
                        <td>{{ $baja->stock_nuevo }}</td>
                        <td>{{ $baja->usuario->nombre ?? '-' }}</td>

                        <td>
                            @if ($baja->estado === 'registrado')
                                <span class="badge badge-success">Registrado</span>
                            @else
                                <span class="badge badge-danger">Anulado</span>
                            @endif
                        </td>

                        <td>{{ $baja->created_at->format('d/m/Y H:i') }}</td>

                        <td>
                            <a href="{{ route('bajas-inventario.show', $baja) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; color: #6B7280;">
                            No hay bajas de inventario registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $bajas->links() }}
    </div>
</div>

@endsection