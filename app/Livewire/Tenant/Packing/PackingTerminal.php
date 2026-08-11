<?php

namespace App\Livewire\Tenant\Packing;

use Livewire\Component;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Tenant\Configuration\PrinterConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PackingTerminal extends Component
{
    // Datos de identificación
    public $userqr = '';
    public $nombre_operador = '---';
    public $is_operador_valid = false;

    // Datos de la orden
    public $opqr = '';
    public $remission = null;
    public $cant_cajas = 1;
    public $msg_op = '';
    public $msg_type = 'success';

    // Historial
    public $historial = [];

    // Configuración
    public $config = null;

    public function mount()
    {
        $this->loadConfig();
    }

    public function loadConfig()
    {
        $this->config = PrinterConfig::getConfig('estacion_empaque', Auth::id());
    }

    public function updatedUserqr($value)
    {
        if (strlen($value) < 1) return;

        // En el sistema multitenant, el ID del usuario es el que se usa como QR
        // Buscamos el usuario en la base central (o como esté configurado)
        $user = \App\Models\Auth\User::find($value);

        if ($user) {
            $this->nombre_operador = $user->name . ' ' . ($user->lastName ?? '');
            $this->is_operador_valid = true;
            $this->dispatch('operador-identificado');
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Operador: ' . $this->nombre_operador]);
        } else {
            $this->nombre_operador = '---';
            $this->is_operador_valid = false;
            $this->userqr = '';
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Código de usuario no válido']);
        }
    }

    public function updatedOpqr($value)
    {
        if (strlen($value) < 3) return;

        $value = strtoupper($value);
        if (str_contains($value, 'OPX')) {
            $id_op = str_replace('OPX', '', $value);
            $this->validarOP($id_op);
        } else {
            $this->msg_op = 'Formato de OP no válido. Debe contener OPX';
            $this->msg_type = 'error';
            $this->opqr = '';
        }
    }

    public function validarOP($id_op)
    {
        $remission = InvRemissions::with('authorizations')->find($id_op);

        if (!$remission) {
            $this->msg_op = "OP #$id_op no encontrada";
            $this->msg_type = 'error';
            $this->opqr = '';
            return;
        }

        // Verificar si ya está empacada
        if ($remission->status === 'EMPACADO' || $remission->status === 'ENTREGADO A RUTA' || $remission->status === 'ENTREGADO') {
            $this->dispatch('confirmar-reversa', ['id_op' => $id_op]);
            $this->opqr = '';
            return;
        }

        // Verificar autorizaciones de Cartera (Empaque requerido para Empacado)
        $hasEmpaque = $remission->authorizations->where('auth_type', 'empaque')->sortByDesc('id')->first()?->status ?? false;
        $hasDespacho = $remission->authorizations->where('auth_type', 'despacho')->sortByDesc('id')->first()?->status ?? false;
        $hasPago = $remission->authorizations->where('auth_type', 'pago')->sortByDesc('id')->first()?->status ?? false;

        if (!$hasEmpaque && !$hasDespacho && !$hasPago) {
            $this->msg_op = "BLOQUEO: Esta orden no tiene autorización de EMPAQUE por parte de Cartera.";
            $this->msg_type = 'error';
            $this->opqr = '';
            return;
        }

        $this->remission = $remission;
        $this->msg_op = "OP #$id_op detectada";
        $this->msg_type = 'success';
        $this->dispatch('op-detectada');
    }

    public function registrarEmpaque()
    {
        if (!$this->remission || !$this->is_operador_valid) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Datos incompletos']);
            return;
        }

        if (!$this->config || !$this->config->proxy_url) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debe configurar la impresora primero']);
            return;
        }

        // 1. Actualizar estado en DB
        $this->remission->update(['status' => 'EMPACADO']);

        // 2. Preparar datos para impresión
        $sucursal = $this->remission->quote?->branch;

        $datosEnvio = [
            'id_op' => $this->remission->id,
            'cant_cajas' => $this->cant_cajas,
            'tipo' => 1, // Detallado
            'impresora' => $this->config->printer_name,
            'cliente' => $this->remission->quote?->customer?->name ?? 'SIN CLIENTE',
            'nit' => $this->remission->quote?->customer?->document ?? '',
            'telefono' => $sucursal?->phone ?? $this->remission->quote?->customer?->phone ?? '',
            'direccion' => $sucursal?->address ?? $this->remission->quote?->customer?->address ?? '',
            'ciudad' => $sucursal?->city?->name ?? $this->remission->quote?->customer?->city?->name ?? '',
            'depto' => $sucursal?->city?->state?->name ?? $this->remission->quote?->customer?->city?->state?->name ?? '',
        ];

        // 3. Disparar evento para que el JS envíe la petición al proxy (para evitar problemas de CORS desde PHP)
        $this->dispatch('imprimir-rotulo', [
            'url' => $this->config->proxy_url,
            'data' => $datosEnvio
        ]);

        // 4. Agregar al historial local
        array_unshift($this->historial, [
            'op' => $this->remission->id,
            'cajas' => $this->cant_cajas,
            'hora' => now()->format('H:i')
        ]);
        $this->historial = array_slice($this->historial, 0, 5);

        // 5. Limpiar para siguiente escaneo
        $this->opqr = '';
        $this->remission = null;
        $this->cant_cajas = 1;
        $this->msg_op = '';
        
        $this->dispatch('empaque-registrado');
    }

    public function resetOperador()
    {
        $this->userqr = '';
        $this->nombre_operador = '---';
        $this->is_operador_valid = false;
        $this->remission = null;
        $this->opqr = '';
    }

    public function render()
    {
        return view('livewire.tenant.packing.packing-terminal')->layout('layouts.app');
    }
}
