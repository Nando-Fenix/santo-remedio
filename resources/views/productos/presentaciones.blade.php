@extends('layouts.app')

@section('title', 'Presentaciones | Santo Remedio')
@section('page-title', 'Presentaciones del producto')
@section('page-subtitle', 'Precios, equivalencias y código de barras')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">
                {{ $producto->nombre_comercial }}
            </h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                {{ $producto->nombre_generico ?? 'Sin nombre genérico' }}
                @if($producto->concentracion)
                    — {{ $producto->concentracion }}
                @endif
            </p>
        </div>

        @if (auth()->user()->tienePermiso('ver_productos'))
            <a href="{{ route('productos.index') }}" class="btn-secondary">
                Volver a productos
            </a>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert-danger">
        <strong>Revise los siguientes errores:</strong>
        <ul style="margin-bottom: 0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (auth()->user()->tienePermiso('editar_producto'))
    <div class="card" style="margin-bottom: 22px;">
        <h3 style="margin-top: 0; color: #4C1D95;">Agregar presentación</h3>

        <form method="POST" action="{{ route('productos.presentaciones.store', $producto) }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Presentación *</label>
                    <select name="presentacion_id">
                        <option value="">Seleccione</option>
                        @foreach ($presentaciones as $presentacion)
                            <option value="{{ $presentacion->id }}" @selected(old('presentacion_id') == $presentacion->id)>
                                {{ $presentacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Nombre mostrado *</label>
                    <input type="text" name="nombre_mostrado" value="{{ old('nombre_mostrado') }}" placeholder="Ej: Caja x 100 tabletas">
                </div>

                <div class="form-group">
                    <label>Unidades equivalentes *</label>
                    <input type="number" min="1" name="unidades_equivalentes" value="{{ old('unidades_equivalentes', 1) }}">
                </div>

                <div class="form-group">
                    <label>Precio de compra *</label>
                    <input type="number" step="0.01" min="0" name="precio_compra" value="{{ old('precio_compra', 0) }}">
                </div>

                <div class="form-group">
                    <label>Precio de venta *</label>
                    <input type="number" step="0.01" min="0" name="precio_venta" value="{{ old('precio_venta', 0) }}">
                </div>

                <div class="form-group">
                    <label>Código de barras</label>
                    <input 
                        type="text" 
                        id="codigo_barras"
                        name="codigo_barras" 
                        value="{{ old('codigo_barras') }}" 
                        placeholder="Escanee el código del producto"
                    >
                </div>

                <button type="button" class="btn-secondary" onclick="activarEscaner()">
                    Escanear código
                </button>

                <script>
                    function activarEscaner() {
                        const input = document.getElementById('codigo_barras');
                        input.focus();
                        input.select();
                    }
                </script>
            </div>

            <label class="checkbox-line" style="margin-top: 18px;">
                <input type="checkbox" name="es_principal" value="1" @checked(old('es_principal'))>
                Usar como presentación rápida/principal para ventas
            </label>

            <div style="margin-top: 22px;">
                @if (auth()->user()->tienePermiso('editar_producto'))
                    <button class="btn-primary" type="submit">
                        Guardar presentación
                    </button>
                @endif
            </div>
        </form>
    </div>
@endif

<div class="card">
    <h3 style="margin-top: 0; color: #4C1D95;">Presentaciones registradas</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Presentación</th>
                    <th>Nombre mostrado</th>
                    <th>Equivalencia</th>
                    <th>Compra</th>
                    <th>Venta</th>
                    <th>Códigos</th>
                    <th>Principal</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($producto->presentaciones as $item)
                    <tr>
                        <td>{{ $item->presentacion->nombre ?? '-' }}</td>
                        <td>{{ $item->nombre_mostrado }}</td>
                        <td>{{ $item->unidades_equivalentes }}</td>
                        <td>{{ number_format($item->precio_compra, 2) }} Bs</td>
                        <td>{{ number_format($item->precio_venta, 2) }} Bs</td>
                        <td>
                            @forelse ($item->codigosBarras as $codigo)
                                <span class="badge badge-soft">{{ $codigo->codigo }}</span>
                            @empty
                                -
                            @endforelse
                        </td>
                        <td>
                            @if ($item->es_principal)
                                <span class="badge badge-success">Sí</span>
                            @else
                                <span class="badge badge-soft">No</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $item->estado === 'activo' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($item->estado) }}
                            </span>
                        </td>
                        <td>
                            @if ($item->estado === 'activo' && auth()->user()->tienePermiso('desactivar_producto'))
                                <form method="POST"
                                    action="{{ route('productos.presentaciones.destroy', [$producto, $item]) }}"
                                    onsubmit="return confirmarFormulario(event,'¿Desactivar esta presentación?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn-danger" type="submit">
                                        Desactivar
                                    </button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6B7280;">
                            Este producto todavía no tiene presentaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection