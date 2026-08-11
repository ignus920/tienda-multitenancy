<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Tenant\Auth\AccessControlService;
use Illuminate\Support\Facades\Auth;

class CheckTenantAccessControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Solo verificamos si hay un usuario autenticado y estamos en un contexto de tenant
        // (La conexión tenant ya debe estar establecida por el middleware anterior)
        if ($user && session()->has('tenant_id')) {
            $tenant = \App\Models\Auth\Tenant::find(session('tenant_id'));

            // Si el tenant no existe o no ha completado la configuración de su BD, omitimos la validación
            // Esto evita errores de "Unknown database" en la ruta /company/setup o similares
            if (!$tenant || !$tenant->database_setup) {
                return $next($request);
            }

            $accessService = new AccessControlService();
            $result = $accessService->checkAccess($user);

            if (!$result['status']) {
                // Si la petición espera JSON (como llamadas API o algunas de Livewire)
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Acceso denegado',
                        'message' => $result['message'],
                        'code' => $result['code']
                    ], 403);
                }

                // De lo contrario, mostrar error 403 con mensaje personalizado
                abort(403, $result['message']);
            }
        }

        return $next($request);
    }
}
