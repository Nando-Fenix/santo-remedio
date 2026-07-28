@extends('layouts.app')

@section('title', 'Crear usuario | Santo Remedio')
@section('page-title', 'Crear usuario')
@section('page-subtitle', 'Registro de usuario con rol, sucursal y permisos')

@section('content')

@if (!auth()->user()->tienePermiso('administrar_usuarios'))
    <div class="alert-danger">
        No tiene permiso para administrar usuarios.
    </div>
@else

@if ($errors->any())
    <div class="alert-danger">
        <strong>Revise los siguientes errores:</strong>
        <ul style="margin-bottom:0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('usuarios.store') }}" class="user-form">
    @csrf

    <div class="user-three-layout">

        {{-- COLUMNA IZQUIERDA --}}
        <section class="user-panel">
            <div class="user-section-head">
                <div>
                    <h3>
                        <i class="bi bi-person-vcard"></i>
                        Datos de acceso
                    </h3>
                    <small>Información personal y credenciales</small>
                </div>
            </div>

            <div class="user-compact-grid">
                <div class="form-group user-full">
                    <label>Nombre completo *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required>
                </div>

                <div class="form-group">
                    <label>CI *</label>
                    <input type="text" name="ci" value="{{ old('ci') }}" required>
                </div>

                <div class="form-group">
                    <label>Usuario *</label>
                    <input type="text" name="usuario" value="{{ old('usuario') }}" required>
                </div>

                <div class="form-group user-full">
                    <label>Contraseña *</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group user-full">
                    <label>Confirmar contraseña *</label>
                    <input type="password" name="password_confirmation" required>
                </div>
            </div>
        </section>

        {{-- COLUMNA CENTRAL --}}
        <section class="user-panel">
            <div class="user-section-head">
                <div>
                    <h3>
                        <i class="bi bi-person-badge"></i>
                        Rol y sucursales
                    </h3>
                    <small>Rol principal y sucursales permitidas</small>
                </div>
            </div>

            <div class="user-compact-grid">
                <div class="form-group user-full">
                    <label>Rol *</label>
                    <select name="rol_id" id="rol_id" required>
                        <option value="">Seleccione rol...</option>
                        @foreach ($roles as $rol)
                            <option
                                value="{{ $rol->id }}"
                                data-nombre="{{ $rol->nombre }}"
                                {{ old('rol_id') == $rol->id ? 'selected' : '' }}
                            >
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <div class="user-role-actions">
                        <small class="user-small-help">
                            Administrador tiene acceso total.
                        </small>

                        <button type="button" class="user-link-action" onclick="crearRolRapido()">
                            <i class="bi bi-plus-circle"></i>
                            Nuevo rol
                        </button>
                    </div>
                </div>

                <div class="form-group user-full">
                    <label>Sucursal principal *</label>
                    <select name="sucursal_principal_id" required>
                        <option value="">Seleccione sucursal...</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ old('sucursal_principal_id') == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="user-branch-compact">
                <h4>Sucursales permitidas</h4>

                <div class="user-branch-compact-list">
                    @foreach ($sucursales as $sucursal)
                        <label class="user-compact-check">
                            <input
                                type="checkbox"
                                name="sucursales[]"
                                value="{{ $sucursal->id }}"
                                {{ in_array($sucursal->id, old('sucursales', [])) ? 'checked' : '' }}
                            >
                            <span>{{ $sucursal->nombre }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- COLUMNA DERECHA --}}
        <section class="user-panel user-permissions-panel" id="bloque_permisos">
            <div class="user-section-head user-section-head-actions">
                <div>
                    <h3>
                        <i class="bi bi-shield-check"></i>
                        Permisos
                    </h3>
                    <small>Permisos resumidos por módulo</small>
                </div>

                <div class="user-mini-actions">
                    <button type="button" class="btn-secondary" onclick="marcarTodosPermisos()">
                        Todos
                    </button>

                    <button type="button" class="btn-secondary" onclick="desmarcarTodosPermisos()">
                        Ninguno
                    </button>
                </div>
            </div>

            <div class="permission-compact-modules">
                @foreach ($permisos as $modulo => $items)
                    <details class="permission-compact-module">
                        <summary>
                            <div>
                                <strong>{{ $modulo }}</strong>
                                <small>{{ $items->count() }} permiso(s)</small>
                            </div>

                            <i class="bi bi-chevron-down"></i>
                        </summary>

                        <div class="permission-compact-body">
                            @foreach ($items as $permiso)
                                <label class="permission-mini-item" title="{{ $permiso->nombre }}">
                                    <input
                                        type="checkbox"
                                        class="permiso-checkbox"
                                        name="permisos[]"
                                        value="{{ $permiso->id }}"
                                        {{ in_array($permiso->id, old('permisos', [])) ? 'checked' : '' }}
                                    >

                                    <span>{{ $permiso->descripcion }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </section>

    </div>

    <div class="user-form-actions">
        <a href="{{ route('usuarios.index') }}" class="btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Cancelar
        </a>

        <button type="submit" class="btn-primary">
            <i class="bi bi-check2-circle"></i>
            Guardar usuario
        </button>
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

    async function crearRolRapido() {
        const { value: nombre } = await Swal.fire({
            title: 'Nuevo rol',
            input: 'text',
            inputLabel: 'Nombre del rol',
            inputPlaceholder: 'Ej. Encargado de caja',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#6D28D9',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Debe ingresar el nombre del rol.';
                }
            }
        });

        if (!nombre) {
            return;
        }

        try {
            const respuesta = await fetch(`{{ route('usuarios.roles-rapido') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    nombre: nombre.trim(),
                }),
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok) {
                let mensaje = 'No se pudo crear el rol.';

                if (resultado.errors) {
                    mensaje = Object.values(resultado.errors).flat().join('\n');
                } else if (resultado.message) {
                    mensaje = resultado.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje,
                    confirmButtonColor: '#6D28D9'
                });

                return;
            }

            const selectRol = document.getElementById('rol_id');

            const option = document.createElement('option');
            option.value = resultado.rol.id;
            option.textContent = resultado.rol.nombre;
            option.dataset.nombre = resultado.rol.nombre;
            option.selected = true;

            selectRol.appendChild(option);

            actualizarBloquePermisos();

            Swal.fire({
                icon: 'success',
                title: 'Rol creado',
                text: 'El rol fue creado y seleccionado.',
                confirmButtonColor: '#6D28D9'
            });

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error inesperado',
                text: error.message,
                confirmButtonColor: '#6D28D9'
            });
        }
    }

    document.getElementById('rol_id').addEventListener('change', actualizarBloquePermisos);
    actualizarBloquePermisos();
</script>

@endsection