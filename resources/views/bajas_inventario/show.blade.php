@extends('layouts.app')

@section('title', 'Detalle de baja de inventario | Santo Remedio')
@section('page-title', 'Detalle de baja de inventario')
@section('page-subtitle', 'Información del producto retirado del inventario')

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
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">
                Baja de inventario #{{ $bajaInventario->id }}
            </h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Registrada el {{ $bajaInventario->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if ($bajaInventario->estado === 'registrado' && auth()->user()->tienePermiso('anular_baja_inventario'))
                <a href="{{ route('bajas-inventario.anular.create', $bajaInventario) }}" class="btn-danger">
                    Anular baja
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_bajas_inventario'))
                <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                    Volver
                </a>
            @endif
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Cantidad retirada</span>
        <h3>{{ $bajaInventario->cantidad }}</h3>
    </div>

    <div class="stat-card">
        <span>Stock anterior</span>
        <h3>{{ $bajaInventario->stock_anterior }}</h3>
    </div>

    <div class="stat-card">
        <span>Stock nuevo</span>
        <h3>{{ $bajaInventario->stock_nuevo }}</h3>
    </div>

    <div class="stat-card">
        <span>Estado</span>
        <h3 style="font-size: 18px;">
            {{ ucfirst($bajaInventario->estado) }}
        </h3>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Producto retirado</h3>

    <p>
        <strong>Producto:</strong>
        {{ $bajaInventario->producto->nombre_comercial ?? '-' }}

        @if ($bajaInventario->producto?->nombre_generico)
            <br>
            <strong>Nombre genérico:</strong>
            {{ $bajaInventario->producto->nombre_generico }}
        @endif

        @if ($bajaInventario->producto?->concentracion)
            <br>
            <strong>Concentración:</strong>
            {{ $bajaInventario->producto->concentracion }}
        @endif

        @if ($bajaInventario->producto?->laboratorio)
            <br>
            <strong>Laboratorio:</strong>
            {{ $bajaInventario->producto->laboratorio->nombre }}
        @endif

        <br>
        <strong>Sucursal:</strong>
        {{ $bajaInventario->sucursal->nombre ?? '-' }}

        <br>
        <strong>Lote:</strong>
        {{ $bajaInventario->lote->numero_lote ?? 'Sin lote' }}

        @if ($bajaInventario->lote?->fecha_vencimiento)
            <br>
            <strong>Vencimiento:</strong>
            {{ $bajaInventario->lote->fecha_vencimiento->format('d/m/Y') }}
        @endif
    </p>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Motivo y observación</h3>

    <p>
        <strong>Motivo:</strong>
        @if ($bajaInventario->motivo === 'vencimiento')
            Vencimiento
        @elseif ($bajaInventario->motivo === 'danado')
            Dañado
        @elseif ($bajaInventario->motivo === 'perdido')
            Perdido
        @elseif ($bajaInventario->motivo === 'ajuste_autorizado')
            Ajuste autorizado
        @else
            Otro
        @endif

        <br>
        <strong>Observación:</strong>
        {{ $bajaInventario->observacion ?? '-' }}

        <br>
        <strong>Registrado por:</strong>
        {{ $bajaInventario->usuario->nombre ?? '-' }}
    </p>
</div>

@if ($bajaInventario->estado === 'anulado')
    <div class="card">
        <h3 style="margin-top: 0; color: #991B1B;">Anulación</h3>

        <p>
            <strong>Anulado por:</strong>
            {{ $bajaInventario->usuarioAnulacion->nombre ?? '-' }}

            <br>
            <strong>Fecha de anulación:</strong>
            {{ $bajaInventario->fecha_anulacion?->format('d/m/Y H:i') ?? '-' }}

            <br>
            <strong>Motivo de anulación:</strong>
            {{ $bajaInventario->motivo_anulacion ?? '-' }}
        </p>
    </div>
@endif

@endsection