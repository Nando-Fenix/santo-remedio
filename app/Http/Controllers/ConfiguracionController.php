<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        $configuracion = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'nombre_farmacia' => 'Santo Remedio',
                'moneda' => 'Bs',
            ]
        );

        return view('configuracion.edit', compact('configuracion'));
    }

    public function update(Request $request)
    {
        $configuracion = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'nombre_farmacia' => 'Santo Remedio',
                'moneda' => 'Bs',
            ]
        );

        $datos = $request->validate([
            'nombre_farmacia' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'mensaje_recibo' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'eliminar_logo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('eliminar_logo')) {
            if ($configuracion->logo && Storage::disk('public')->exists($configuracion->logo)) {
                Storage::disk('public')->delete($configuracion->logo);
            }

            $datos['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($configuracion->logo && Storage::disk('public')->exists($configuracion->logo)) {
                Storage::disk('public')->delete($configuracion->logo);
            }

            $datos['logo'] = $request->file('logo')->store('configuracion', 'public');
        }

        unset($datos['eliminar_logo']);

        $configuracion->update($datos);

        return redirect()
            ->route('configuracion.edit')
            ->with('success', 'Configuración actualizada correctamente.');
    }
}