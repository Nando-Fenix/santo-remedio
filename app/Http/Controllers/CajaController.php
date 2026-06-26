<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ArqueoDenominacion;
use App\Models\CierreCaja;

class CajaController extends Controller
{
    private function obtenerSucursalActual()
    {
        $user = auth()->user();

        return $user->sucursalPrincipal()->first()
            ?? $user->sucursales()->wherePivot('estado', 'activo')->first();
    }

    private function obtenerCajaAbiertaPorSucursal(int $sucursalId)
    {
        return Caja::where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();
    }

    public function index()
    {
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $cajaAbierta = Caja::with(['sucursal', 'usuario', 'turno'])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        $cajas = Caja::with(['sucursal', 'usuario', 'turno'])
            ->where('sucursal_id', $sucursal->id)
            ->latest('fecha_apertura')
            ->paginate(10);

        return view('caja.index', compact('sucursal', 'cajaAbierta', 'cajas'));
    }

    public function create()
    {
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $cajaAbierta = $this->obtenerCajaAbiertaPorSucursal($sucursal->id);

        if ($cajaAbierta) {
            return redirect()
                ->route('caja.index')
                ->with('info', 'Ya existe una caja abierta para esta sucursal.');
        }

        $turnos = Turno::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('caja.create', compact('sucursal', 'cajaAbierta', 'turnos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'turno_id' => ['required', 'exists:turnos,id'],
            'monto_inicial' => ['required', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string'],
        ], [
            'turno_id.required' => 'Seleccione un turno.',
            'monto_inicial.required' => 'Ingrese el monto inicial.',
        ]);

        $user = auth()->user();
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return back()->withErrors([
                'sucursal' => 'El usuario no tiene una sucursal asignada.',
            ]);
        }

        $yaExisteCajaAbierta = Caja::where('sucursal_id', $sucursal->id)
            ->where('estado', 'abierta')
            ->exists();

        if ($yaExisteCajaAbierta) {
            return redirect()
                ->route('caja.index')
                ->with('info', 'Ya existe una caja abierta para esta sucursal.');
        }

        DB::transaction(function () use ($datos, $user, $sucursal) {
            $caja = Caja::create([
                'sucursal_id' => $sucursal->id,
                'usuario_id' => $user->id, // Usuario que abrió la caja
                'turno_id' => $datos['turno_id'],
                'fecha_apertura' => now(),
                'fecha_cierre' => null,
                'monto_inicial' => $datos['monto_inicial'],
                'total_efectivo' => 0,
                'total_qr' => 0,
                'total_egresos' => 0,
                'total_reembolsos' => 0,
                'total_final' => $datos['monto_inicial'],
                'estado' => 'abierta',
                'observacion' => $datos['observacion'] ?? null,
            ]);

            MovimientoCaja::create([
                'caja_id' => $caja->id,
                'venta_id' => null,
                'usuario_id' => $user->id,
                'sucursal_id' => $sucursal->id,
                'metodo_pago_id' => null,
                'tipo_movimiento' => 'apertura',
                'monto' => $datos['monto_inicial'],
                'descripcion' => 'Apertura de caja',
                'autorizado_por' => null,
            ]);
        });

        return redirect()
            ->route('caja.index')
            ->with('success', 'Caja abierta correctamente.');
    }

    public function egresoCreate()
    {
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $cajaAbierta = $this->obtenerCajaAbiertaPorSucursal($sucursal->id);

        if (!$cajaAbierta) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'Debe existir una caja abierta en esta sucursal antes de registrar un egreso.');
        }

        return view('caja.egreso', compact('sucursal', 'cajaAbierta'));
    }

    public function egresoStore(Request $request)
    {
        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'descripcion' => ['required', 'string', 'max:500'],
        ], [
            'monto.required' => 'Ingrese el monto del egreso.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'descripcion.required' => 'Ingrese el motivo del egreso.',
        ]);

        $user = auth()->user();
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return back()->withErrors([
                'sucursal' => 'El usuario no tiene una sucursal asignada.',
            ]);
        }

        $cajaAbierta = $this->obtenerCajaAbiertaPorSucursal($sucursal->id);

        if (!$cajaAbierta) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'Debe existir una caja abierta en esta sucursal antes de registrar un egreso.');
        }

        DB::transaction(function () use ($datos, $user, $sucursal, $cajaAbierta) {
            MovimientoCaja::create([
                'caja_id' => $cajaAbierta->id,
                'venta_id' => null,
                'usuario_id' => $user->id,
                'sucursal_id' => $sucursal->id,
                'metodo_pago_id' => null,
                'tipo_movimiento' => 'egreso',
                'monto' => $datos['monto'],
                'descripcion' => $datos['descripcion'],
                'autorizado_por' => null,
            ]);

            $cajaAbierta->increment('total_egresos', $datos['monto']);
            $cajaAbierta->refresh();

            $cajaAbierta->update([
                'total_final' => $cajaAbierta->monto_inicial
                    + $cajaAbierta->total_efectivo
                    - $cajaAbierta->total_egresos
                    - $cajaAbierta->total_reembolsos,
            ]);
        });

        return redirect()
            ->route('caja.index')
            ->with('success', 'Egreso registrado correctamente.');
    }

    public function movimientos(Request $request)
    {
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $tipoMovimiento = $request->get('tipo_movimiento');

        $movimientos = MovimientoCaja::with(['caja', 'venta', 'usuario', 'sucursal', 'metodoPago'])
            ->where('sucursal_id', $sucursal->id)
            ->when($tipoMovimiento, function ($query, $tipoMovimiento) {
                $query->where('tipo_movimiento', $tipoMovimiento);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('caja.movimientos', compact('movimientos', 'sucursal', 'tipoMovimiento'));
    }

    public function cierreCreate()
    {
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'El usuario no tiene una sucursal asignada.');
        }

        $cajaAbierta = Caja::with(['turno', 'movimientos'])
            ->where('sucursal_id', $sucursal->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();

        if (!$cajaAbierta) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'No existe una caja abierta en esta sucursal para cerrar.');
        }

        return view('caja.cierre', compact('sucursal', 'cajaAbierta'));
    }

    public function cierreStore(Request $request)
    {
        $datos = $request->validate([
            'efectivo_contado' => ['required', 'numeric', 'min:0'],
            'qr_verificado' => ['required', 'numeric', 'min:0'],
            'denominaciones' => ['nullable', 'array'],
            'denominaciones.*.denominacion' => ['required_with:denominaciones', 'numeric', 'min:0'],
            'denominaciones.*.cantidad' => ['required_with:denominaciones', 'integer', 'min:0'],
            'observacion' => ['nullable', 'string'],
        ], [
            'efectivo_contado.required' => 'Ingrese el efectivo contado.',
            'qr_verificado.required' => 'Ingrese el monto QR verificado.',
        ]);

        $user = auth()->user();
        $sucursal = $this->obtenerSucursalActual();

        if (!$sucursal) {
            return back()->withErrors([
                'sucursal' => 'El usuario no tiene una sucursal asignada.',
            ]);
        }

        $cajaAbierta = $this->obtenerCajaAbiertaPorSucursal($sucursal->id);

        if (!$cajaAbierta) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'No existe una caja abierta en esta sucursal para cerrar.');
        }

        DB::transaction(function () use ($datos, $user, $sucursal, $cajaAbierta) {
            $efectivoSistema = $cajaAbierta->monto_inicial
                + $cajaAbierta->total_efectivo
                - $cajaAbierta->total_egresos
                - $cajaAbierta->total_reembolsos;

            $qrSistema = $cajaAbierta->total_qr;

            $diferenciaEfectivo = $datos['efectivo_contado'] - $efectivoSistema;
            $diferenciaQr = $datos['qr_verificado'] - $qrSistema;

            $totalSistema = $efectivoSistema + $qrSistema;
            $totalVerificado = $datos['efectivo_contado'] + $datos['qr_verificado'];

            $estado = ($diferenciaEfectivo == 0 && $diferenciaQr == 0)
                ? 'correcto'
                : 'con_diferencia';

            $cierre = \App\Models\CierreCaja::create([
                'caja_id' => $cajaAbierta->id,
                'usuario_id' => $user->id, // Usuario que cerró la caja
                'sucursal_id' => $sucursal->id,
                'turno_id' => $cajaAbierta->turno_id,
                'fecha_cierre' => now(),

                'efectivo_sistema' => $efectivoSistema,
                'efectivo_contado' => $datos['efectivo_contado'],
                'diferencia_efectivo' => $diferenciaEfectivo,

                'qr_sistema' => $qrSistema,
                'qr_verificado' => $datos['qr_verificado'],
                'diferencia_qr' => $diferenciaQr,

                'total_egresos' => $cajaAbierta->total_egresos,
                'total_reembolsos' => $cajaAbierta->total_reembolsos,

                'monto_retiro_ahorro' => 0,
                'efectivo_final_despues_ahorro' => $datos['efectivo_contado'],

                'total_sistema' => $totalSistema,
                'total_verificado' => $totalVerificado,

                'estado' => $estado,
                'observacion' => $datos['observacion'] ?? null,
            ]);

            foreach (($datos['denominaciones'] ?? []) as $item) {
                $cantidad = (int) ($item['cantidad'] ?? 0);
                $denominacion = (float) ($item['denominacion'] ?? 0);

                if ($cantidad <= 0) {
                    continue;
                }

                ArqueoDenominacion::create([
                    'cierre_caja_id' => $cierre->id,
                    'denominacion' => $denominacion,
                    'cantidad' => $cantidad,
                    'total' => $denominacion * $cantidad,
                ]);
            }

            MovimientoCaja::create([
                'caja_id' => $cajaAbierta->id,
                'venta_id' => null,
                'usuario_id' => $user->id,
                'sucursal_id' => $sucursal->id,
                'metodo_pago_id' => null,
                'tipo_movimiento' => 'cierre',
                'monto' => $totalVerificado,
                'descripcion' => 'Cierre de caja. Estado: ' . $estado,
                'autorizado_por' => null,
            ]);

            $cajaAbierta->update([
                'fecha_cierre' => now(),
                'total_final' => $totalVerificado,
                'estado' => 'cerrada',
            ]);
        });

        return redirect()
            ->route('caja.index')
            ->with('success', 'Caja cerrada correctamente.');
    }
}
