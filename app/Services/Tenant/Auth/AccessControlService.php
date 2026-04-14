<?php

namespace App\Services\Tenant\Auth;

use App\Models\Auth\User;
use App\Models\Tenant\CnfAccesoIp;
use App\Models\Tenant\CnfAccesoHorario;
use App\Models\Tenant\CnfLogAcceso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class AccessControlService
{
    /**
     * Verifica si un usuario tiene permiso de acceso basado en IP y Horario.
     * 
     * @param User $user El usuario a validar.
     * @return array Resultado con status y mensaje.
     */
    public function checkAccess(User $user): array
    {
        // 1. Exención para Super Administradores
        if ($user->isSuperAdmin()) {
            return [
                'status' => true, 
                'message' => 'Acceso autorizado (SuperAdmin)',
                'code' => 'SUPER_ADMIN_EXEMPT'
            ];
        }

        $ip = Request::ip();
        $now = Carbon::now('America/Bogota');
        $dayOfWeek = $now->dayOfWeekIso; // 1 (Lunes) a 7 (Domingo)
        $currentTime = $now->format('H:i:s');

        // 2. Validación de IP
        // Si el usuario tiene IPs configuradas, debe estar en una de ellas.
        // Si no tiene ninguna configurada, se asume acceso libre por IP.
        $hasIpsDefined = CnfAccesoIp::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if ($hasIpsDefined) {
            $isIpAllowed = CnfAccesoIp::where('user_id', $user->id)
                ->where('ip_allowed', $ip)
                ->where('is_active', true)
                ->exists();

            if (!$isIpAllowed) {
                $this->logAccess($user->id, $ip, 'ip_denegada');
                return [
                    'status' => false,
                    'type' => 'ip',
                    'message' => "La IP {$ip} no está autorizada para este usuario.",
                    'code' => 'IP_DENIED'
                ];
            }
        }

        // 3. Validación de Horario
        // Si el usuario tiene horarios configurados, debe estar dentro de uno.
        $hasScheduleDefined = CnfAccesoHorario::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if ($hasScheduleDefined) {
            $isWithinSchedule = CnfAccesoHorario::where('user_id', $user->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->where('is_active', true)
                ->exists();

            if (!$isWithinSchedule) {
                $this->logAccess($user->id, $ip, 'horario_denegado');
                return [
                    'status' => false,
                    'type' => 'schedule',
                    'message' => 'Acceso denegado: Fuera del horario laboral permitido.',
                    'code' => 'TIME_DENIED'
                ];
            }
        }

        // 4. Registro de Acceso Exitoso
        $this->logAccess($user->id, $ip, 'exitoso');

        return [
            'status' => true,
            'message' => 'Acceso autorizado.',
            'code' => 'ACCESS_GRANTED'
        ];
    }

    /**
     * Registra el intento de acceso en la bitácora del tenant.
     */
    private function logAccess(int $userId, string $ip, string $type): void
    {
        try {
            CnfLogAcceso::create([
                'user_id' => $userId,
                'ip_address' => $ip,
                'access_type' => $type,
                'user_agent' => Request::userAgent()
            ]);
        } catch (\Exception $e) {
            // Silenciosamente fallar si hay error en el log para no interrumpir el flujo
            \Illuminate\Support\Facades\Log::error("Error al registrar log de acceso: " . $e->getMessage());
        }
    }
}
