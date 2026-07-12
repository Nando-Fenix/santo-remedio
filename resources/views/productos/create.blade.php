@extends('layouts.app')

@section('title', 'Nuevo producto | Santo Remedio')
@section('page-title', 'Nuevo producto')
@section('page-subtitle', 'Registro básico del medicamento')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Registrar producto</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Complete los datos principales del medicamento. Las presentaciones, precios y código de barras se agregarán después.
        </p>
    </div>

    @if (!auth()->user()->tienePermiso('crear_producto'))
        <div class="alert-danger">
            No tiene permiso para crear productos.
        </div>
    @else

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

    <form method="POST" action="{{ route('productos.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre comercial *</label>
                <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}" placeholder="Ej: Paracetamol Forte">
            </div>

            <div class="form-group">
                <label>Nombre genérico</label>
                <input type="text" name="nombre_generico" value="{{ old('nombre_generico') }}" placeholder="Ej: Paracetamol">
            </div>

            <div class="form-group">
                <label>Concentración</label>
                <input type="text" name="concentracion" value="{{ old('concentracion') }}" placeholder="Ej: 500 mg">
            </div>

            <div class="form-group">
                <label for="tipo_producto">Tipo de producto</label>
                <select name="tipo_producto" id="tipo_producto" class="form-control" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="medicamento" {{ old('tipo_producto') == 'medicamento' ? 'selected' : '' }}>Medicamento</option>
                    <option value="insumo_medico" {{ old('tipo_producto') == 'insumo_medico' ? 'selected' : '' }}>Insumo médico</option>
                    <option value="producto_general" {{ old('tipo_producto') == 'producto_general' ? 'selected' : '' }}>Producto general</option>
                    <option value="higiene" {{ old('tipo_producto') == 'higiene' ? 'selected' : '' }}>Higiene</option>
                    <option value="bebe" {{ old('tipo_producto') == 'bebe' ? 'selected' : '' }}>Bebé</option>
                    <option value="otro" {{ old('tipo_producto') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id">
                    <option value="">Seleccione una categoría</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(old('categoria_id') == $categoria->id)>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Laboratorio</label>
                <select name="laboratorio_id">
                    <option value="">Seleccione un laboratorio</option>
                    @foreach ($laboratorios as $laboratorio)
                        <option value="{{ $laboratorio->id }}" @selected(old('laboratorio_id') == $laboratorio->id)>
                            {{ $laboratorio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Proveedor principal</label>
                <select name="proveedor_id">
                    <option value="">Seleccione un proveedor</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" @selected(old('proveedor_id') == $proveedor->id)>
                            {{ $proveedor->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 18px;">
            <label>Descripción o uso</label>
            <textarea name="descripcion" rows="4" placeholder="Ej: Analgésico y antipirético">{{ old('descripcion') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            @if (auth()->user()->tienePermiso('crear_producto'))
                <button type="submit" class="btn-primary">
                    Guardar producto
                </button>
            @endif

            @if (auth()->user()->tienePermiso('ver_productos'))
                <a href="{{ route('productos.index') }}" class="btn-secondary">
                    Cancelar
                </a>
            @endif
        </div>
    </form>
    @endif
</div>

@endsection