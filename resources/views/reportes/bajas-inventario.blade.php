@extends('layouts.app')

@section('title', 'Reporte de bajas de inventario | Santo Remedio')
@section('page-title', 'Reporte de bajas de inventario')
@section('page-subtitle', 'Productos retirados del inventario por vencimiento, daño, pérdida u otros motivos')

@section('content')

<div class="card" style="margin-bottom:22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Reporte de bajas de inventario</h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Sucursal:
                <strong>{{ $sucursal->nombre ?? 'Sin sucursal asignada' }}</strong>
            </p>
        </div>

        <a
            href="{{ route('reportes.bajas-inventario.exportar-csv', request()->query()) }}"
            class="btn-primary"
        >
            Exportar CSV
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:22px;">
    <form method="GET" action="{{ route('reportes.bajas-inventario') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group">
                <label>Buscar</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="N° baja, producto, genérico, concentración o lote"
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

        <div style="display:flex; gap:10px; margin-top:14px;">
            <button type="submit" class="btn-primary">
                Filtrar
            </button>

            <a href="{{ route('reportes.bajas-inventario') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="stats-grid" style="margin-bottom:22px;">
    <div class="stat-card">
        <div class="stat-title">Total registros</div>
        <div class="stat-value">{{ $totalBajas }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Unidades retiradas válidas</div>
        <div class="stat-value">{{ $totalUnidades }}</div>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° baja</th>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Motivo</th>
                    <th>Cantidad</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
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

                        <td>{{ $baja->created_at?->format('d/m/Y H:i') }}</td>

                        <td>
                            <strong>{{ $baja->producto->nombre_comercial ?? '-' }}</strong>

                            @if ($baja->producto?->laboratorio || $baja->producto?->concentracion)
                                <br>
                                <small style="color:#6B7280;">
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
                                <small style="color:#6B7280;">
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

                        <td>{{ $baja->cantidad }}</td>

                        <td>{{ $baja->usuario->nombre ?? '-' }}</td>

                        <td>
                            @if ($baja->estado === 'registrado')
                                <span class="badge badge-success">Registrado</span>
                            @else
                                <span class="badge badge-danger">Anulado</span>
                            @endif
                        </td>

                        <td>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <a href="{{ route('bajas-inventario.show', $baja) }}" class="btn-secondary">
                                    Ver
                                </a>

                                <a href="{{ route('bajas-inventario.recibo', $baja) }}" class="btn-primary" target="_blank">
                                    Imprimir
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6B7280;">
                            No hay bajas de inventario en el rango seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $bajas->links() }}
    </div>
</div>

@endsection
