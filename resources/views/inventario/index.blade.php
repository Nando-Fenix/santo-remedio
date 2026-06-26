@extends('layouts.app')

@section('title', 'Inventario | Santo Remedio')
@section('page-title', 'Inventario')
@section('page-subtitle', 'Control de stock por sucursal, lote y vencimiento')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Stock actual</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Consulta de productos disponibles por sucursal y lote.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if (auth()->user()->tienePermiso('ver_movimientos_inventario'))
                <a href="{{ route('inventario.movimientos') }}" class="btn-secondary">
                    Ver movimientos
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ajustar_inventario'))
                <a href="{{ route('inventario.create') }}" class="btn-primary">
                    + Entrada de inventario
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('inventario.index') }}" style="margin-bottom: 18px;">
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
                <label>Sucursal</label>
                <select name="sucursal_id">
                    <option value="">Todas las sucursales</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" @selected(($sucursalId ?? '') == $sucursal->id)>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('inventario.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Genérico</th>
                    <th>Concentración</th>
                    <th>Sucursal</th>
                    <th>Lote</th>
                    <th>Vencimiento</th>
                    <th>Stock</th>
                    <th>Mínimo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inventarios as $inventario)
                    <tr>
                        <td>{{ $inventario->producto->nombre_comercial ?? '-' }}</td>
                        <td>{{ $inventario->producto->nombre_generico ?? '-' }}</td>
                        <td>{{ $inventario->producto->concentracion ?? '-' }}</td>
                        <td>{{ $inventario->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $inventario->lote->numero_lote ?? 'Sin lote' }}</td>
                        <td>
                            @if ($inventario->lote?->fecha_vencimiento)
                                {{ $inventario->lote->fecha_vencimiento->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <strong>{{ $inventario->stock_actual }}</strong>
                        </td>
                        <td>{{ $inventario->stock_minimo }}</td>
                        <td>
                            @if ($inventario->stock_actual <= 0)
                                <span class="badge badge-danger">Agotado</span>
                            @elseif ($inventario->stock_actual <= $inventario->stock_minimo)
                                <span class="badge badge-warning">Stock bajo</span>
                            @else
                                <span class="badge badge-success">Disponible</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6B7280;">
                            Todavía no hay inventario registrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $inventarios->links() }}
    </div>

</div>

@endsection