@extends('layouts.app')

@section('title', 'Reporte de servicios | Santo Remedio')
@section('page-title', 'Reporte de servicios')
@section('page-subtitle', 'Ingresos, atenciones e insumos consumidos por servicios de farmacia')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Reporte de servicios</h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Revise ingresos por servicios, atenciones realizadas e insumos descontados.
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.servicios.exportar-csv', request()->query()) }}"
                class="btn-primary"
            >
                Exportar atenciones
            </a>

            <a
                href="{{ route('reportes.servicios.exportar-insumos-csv', request()->query()) }}"
                class="btn-secondary"
            >
                Exportar insumos
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                Volver a reportes
            </a>
        </div> 
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Filtros</h3>

    <form method="GET" action="{{ route('reportes.servicios') }}">
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
                <label>Servicio</label>
                <select name="servicio_farmacia_id">
                    <option value="">Todos</option>
                    @foreach ($servicios as $servicio)
                        <option value="{{ $servicio->id }}" @selected((string) $servicioId === (string) $servicio->id)>
                            {{ $servicio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="completada" @selected($estado === 'completada')>Completada</option>
                    <option value="anulada" @selected($estado === 'anulada')>Anulada</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.servicios') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Total atenciones</span>
        <h3>{{ $resumen['total_atenciones'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Completadas</span>
        <h3>{{ $resumen['atenciones_completadas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Anuladas</span>
        <h3>{{ $resumen['atenciones_anuladas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Ingresos válidos</span>
        <h3>{{ number_format($resumen['ingresos_completados'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Monto anulado</span>
        <h3>{{ number_format($resumen['ingresos_anulados'], 2) }} Bs</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 22px;">
    <div class="card">
        <h3 style="margin-top:0; color:#4C1D95;">Servicios más vendidos</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Cantidad</th>
                        <th>Atenciones</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($serviciosMasVendidos as $item)
                        <tr>
                            <td>{{ $item->servicio->nombre ?? '-' }}</td>
                            <td>{{ $item->cantidad_total }}</td>
                            <td>{{ $item->atenciones_total }}</td>
                            <td>{{ number_format($item->ingresos_total, 2) }} Bs</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; color:#6B7280;">
                                No hay servicios completados en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0; color:#4C1D95;">Insumos consumidos</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Presentación</th>
                        <th>Unidades</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($insumosConsumidos as $item)
                        <tr>
                            <td>
                                <strong>
                                    {{ $item->productoPresentacion->nombre_mostrado ?? $item->producto->nombre_comercial ?? '-' }}
                                </strong>

                                @if ($item->producto?->laboratorio || $item->producto?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        @if ($item->producto?->laboratorio)
                                            {{ $item->producto->laboratorio->nombre }}
                                        @endif

                                        @if ($item->producto?->laboratorio && $item->producto?->concentracion)
                                            |
                                        @endif

                                        @if ($item->producto?->concentracion)
                                            {{ $item->producto->concentracion }}
                                        @endif
                                    </small>
                                @endif
                            </td>

                            <td>{{ $item->productoPresentacion->presentacion->nombre ?? '-' }}</td>
                            <td>{{ $item->unidades_total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center; color:#6B7280;">
                                No hay insumos consumidos en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de atenciones</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Método</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Ver</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($atenciones as $atencion)
                    <tr>
                        <td>{{ $atencion->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td>{{ $atencion->servicio->nombre ?? '-' }}</td>
                        <td>{{ $atencion->cliente->nombre ?? 'Consumidor final' }}</td>
                        <td>{{ $atencion->cantidad }}</td>
                        <td>{{ number_format($atencion->total, 2) }} Bs</td>
                        <td>{{ $atencion->metodoPago->nombre ?? '-' }}</td>
                        <td>{{ $atencion->usuario->nombre ?? '-' }}</td>

                        <td>
                            @if ($atencion->estado === 'completada')
                                <span class="badge badge-success">Completada</span>
                            @else
                                <span class="badge badge-danger">Anulada</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('atenciones-servicio.show', $atencion) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6B7280;">
                            No hay atenciones en el rango seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $atenciones->links() }}
    </div>
</div>

@endsection