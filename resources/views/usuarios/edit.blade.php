@extends('layouts.app')

@section('title', 'Editar usuario | Santo Remedio')
@section('page-title', 'Editar usuario')
@section('page-subtitle', 'Actualización de acceso, rol, sucursales y permisos')

@section('content')

@if (!auth()->user()->tienePermiso('administrar_usuarios'))
    <div class="card">
        <div class="alert-danger">
            No tiene permiso para administrar usuarios.
        </div>
    </div>
@else

<form method="POST" action="{{ route('usuarios.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="card" style="margin-bottom: 22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:14px;">
            <div>
                <h2 style="margin:0; color:#4C1D95;">Editar usuario</h2>
                <p style="margin:6px 0 0; color:#6B7280;">
                    Usuario de acceso: <strong>{{ $user->usuario }}</strong>
                </p>
            </div>

            @if (auth()->user()->tienePermiso('administrar_usuarios'))
                <a href="{{ route('usuarios.index') }}" class="btn-secondary">
                    Volver
                </a>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="card" style="margin-bottom:22px;">
            <div class="alert-danger">
                <strong>Revise los siguientes errores:</strong>
                <ul style="margin-bottom:0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card" style="margin-bottom: 22px;">
        <h3 style="margin-top:0; color:#4C1D95;">1. Datos del usuario</h3>

        <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="form-group">
                <label>Nombre completo *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $user->nombre) }}" required>
            </div>

            <div class="form-group">
                <label>CI *</label>
                <input type="text" name="ci" value="{{ old('ci', $user->ci) }}" required>
            </div>

            <div class="form-group">
                <label>Usuario de acceso *</label>
                <input type="text" name="usuario" value="{{ old('usuario', $user->usuario) }}" required>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="password">
                <small style="color:#6B7280;">Dejar vacío si no desea cambiarla.</small>
            </div>

            <div class="form-group">
                <label>Confirmar nueva contraseña</label>
                <input type="password" name="password_confirmation">
            </div>

            <div class="form-group">
                <label>Estado *</label>
                <select name="estado" required>
                    <option value="activo" {{ old('estado', $user->estado) === 'activo' ? 'selected' : '' }}>
                        Activo
                    </option>
                    <option value="inactivo" {{ old('estado', $user->estado) === 'inactivo' ? 'selected' : '' }}>
                        Inactivo
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 22px;">
        <h3 style="margin-top:0; color:#4C1D95;">2. Rol y sucursales</h3>

        <div class="grid" style="grid-template-columns: repeat(2, 1fr);">
            <div class="form-group">
                <label>Rol *</label>
                <select name="rol_id" id="rol_id" required>
                    <option value="">Seleccione rol...</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}"
                                data-nombre="{{ $rol->nombre }}"
                                {{ old('rol_id', $user->rol_id) == $rol->id ? 'selected' : '' }}>
                            {{ $rol->nombre }}
                        </option>
                    @endforeach
                </select>

                <small style="color:#6B7280;">
                    El administrador tiene acceso total automáticamente.
                </small>
            </div>

            <div class="form-group">
                <label>Sucursal principal *</label>
                <select name="sucursal_principal_id" required>
                    <option value="">Seleccione sucursal...</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}"
                                {{ old('sucursal_principal_id', $sucursalPrincipalId) == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <h4 style="color:#374151; margin-bottom:10px;">Sucursales permitidas</h4>

        <div class="checkbox-grid">
            @foreach ($sucursales as $sucursal)
                <label class="checkbox-card">
                    <input type="checkbox"
                           name="sucursales[]"
                           value="{{ $sucursal->id }}"
                           {{ in_array($sucursal->id, old('sucursales', $sucursalesUsuario)) ? 'checked' : '' }}>
                    <span>{{ $sucursal->nombre }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="card" style="margin-bottom: 22px;" id="bloque_permisos">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:14px;">
            <div>
                <h3 style="margin-top:0; color:#4C1D95;">3. Permisos personalizados</h3>
                <p style="margin:6px 0 0; color:#6B7280;">
                    Estos permisos son directos para este usuario. El administrador tiene acceso total aunque no se marque nada.
                </p>
            </div>

            <div style="display:flex; gap:8px;">
                <button type="button" class="btn-secondary" onclick="marcarTodosPermisos()">
                    Marcar todos
                </button>

                <button type="button" class="btn-secondary" onclick="desmarcarTodosPermisos()">
                    Desmarcar
                </button>
            </div>
        </div>

        <div style="margin-top:18px;">
            @foreach ($permisos as $modulo => $items)
                <div class="permission-module">
                    <h4>{{ $modulo }}</h4>

                    <div class="checkbox-grid">
                        @foreach ($items as $permiso)
                            <label class="checkbox-card">
                                <input type="checkbox"
                                       class="permiso-checkbox"
                                       name="permisos[]"
                                       value="{{ $permiso->id }}"
                                       {{ in_array($permiso->id, old('permisos', $permisosUsuario)) ? 'checked' : '' }}>
                                <span>
                                    <strong>{{ $permiso->descripcion }}</strong>
                                    <br>
                                    <small>{{ $permiso->nombre }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div style="display:flex; gap:12px;">
            @if (auth()->user()->tienePermiso('administrar_usuarios'))
                <button type="submit" class="btn-primary">
                    Guardar cambios
                </button>
            @endif

            <a href="{{ route('usuarios.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </div>
</form>
@endif
<script>
    function marcarTodosPermisos() {
        document.querySelectorAll('.permiso-checkbox').forEach(input => input.checked = true);
    }

    function desmarcarTodosPermisos() {
        document.querySelectorAll('.permiso-checkbox').forEach(input => input.checked = false);
    }

    function actualizarBloquePermisos() {
        const selectRol = document.getElementById('rol_id');
        const selectedOption = selectRol.options[selectRol.selectedIndex];
        const rolNombre = selectedOption?.dataset?.nombre || '';
        const bloquePermisos = document.getElementById('bloque_permisos');

        if (rolNombre === 'Administrador') {
            bloquePermisos.style.opacity = '0.55';
        } else {
            bloquePermisos.style.opacity = '1';
        }
    }

    document.getElementById('rol_id').addEventListener('change', actualizarBloquePermisos);
    actualizarBloquePermisos();
</script>

@endsection