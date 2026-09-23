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

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-box-arrow-down"></i>
                Bajas de inventario
            </h2>

            <p>
                Historial de productos retirados por vencimiento, daño, pérdida u otros motivos.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('registrar_baja_inventario'))
            <a href="{{ route('bajas-inventario.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Registrar baja
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('bajas-inventario.index') }}" class="filter-bar">
        <div class="filter-search">
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

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Motivo</th>
                    <th>Stock</th>
                    <th>Sucursal</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th class="table-actions-cell">Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($bajas as $baja)
                    <tr>
                        <td>
                            <strong>
                                {{ $baja->numero_baja ?? 'BAJ-' . str_pad($baja->id, 6, '0', STR_PAD_LEFT) }}
                            </strong>
                        </td>

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

                        <td>
                            <strong>{{ $baja->cantidad }}</strong>
                            <br>
                            <small style="color:#6B7280;">
                                {{ $baja->stock_anterior }} → {{ $baja->stock_nuevo }}
                            </small>
                        </td>

                        <td>{{ $baja->sucursal->nombre ?? '-' }}</td>

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
                            <div class="action-group">
                                <a
                                    href="{{ route('bajas-inventario.show', $baja) }}"
                                    class="icon-action icon-action-primary"
                                    title="Ver detalle"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                                <button
                                    type="button"
                                    class="icon-action icon-action-print"
                                    onclick="abrirModalImpresion('{{ route('bajas-inventario.recibo', $baja) }}')"
                                    title="Imprimir recibo"
                                >
                                    <i class="bi bi-printer"></i>
                                </button>

                                @if ($baja->estado === 'registrado' && auth()->user()->tienePermiso('anular_baja_inventario'))
                                    <a
                                        href="{{ route('bajas-inventario.anular.create', $baja) }}"
                                        class="icon-action icon-action-danger"
                                        title="Anular baja"
                                    >
                                        <i class="bi bi-x-octagon"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="empty-table-message">
                            No hay bajas de inventario registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $bajas->links() }}
    </div>
</div>

@endsection