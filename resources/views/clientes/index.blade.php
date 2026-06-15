@extends('layouts.app')

@section('title', 'Clientes | Santo Remedio')
@section('page-title', 'Clientes')
@section('page-subtitle', 'Registro y administración de clientes de la farmacia')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Clientes registrados</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Administre los datos de clientes para historial, descuentos y fidelización.
            </p>
        </div>

        <a href="{{ route('clientes.create') }}" class="btn-primary">
            Nuevo cliente
        </a>
    </div>

    <form method="GET" action="{{ route('clientes.index') }}" style="margin-top: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar cliente</label>
                <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Nombre, CI/NIT, teléfono o dirección">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="activo" {{ $estado === 'activo' ? 'selected' : '' }}>Activos</option>
                    <option value="inactivo" {{ $estado === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                    <option value="" {{ $estado === '' ? 'selected' : '' }}>Todos</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('clientes.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>CI/NIT</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Tipo</th>
                    <th>Descuento</th>
                    <th>Estado</th>
                    <th style="width: 180px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $cliente)
                    <tr>
                        <td>
                            <strong>{{ $cliente->nombre }}</strong>
                        </td>
                        <td>{{ $cliente->ci_nit ?? '-' }}</td>
                        <td>{{ $cliente->telefono ?? '-' }}</td>
                        <td>{{ $cliente->direccion ?? '-' }}</td>
                        <td>{{ ucfirst($cliente->tipo_cliente) }}</td>
                        <td>{{ number_format($cliente->descuento_default, 2) }}%</td>
                        <td>
                            @if ($cliente->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('clientes.show', $cliente) }}" class="btn-secondary">
                                    Ver
                                </a>
                                <a href="{{ route('clientes.edit', $cliente) }}" class="btn-secondary">
                                    Editar
                                </a>

                                @if ($cliente->estado === 'activo')
                                    <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" onsubmit="return confirmarFormulario(event, '¿Desea desactivar este cliente?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-danger">
                                            Desactivar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #6B7280;">
                            No hay clientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $clientes->links() }}
    </div>
</div>

@endsection