<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWarehouseSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado y tiene la marca de selección pendiente
        if (auth()->check() && session('needs_warehouse_selection')) {
            // Permitir acceso a rutas de login, logout y verificación
            $allowedRoutes = ['login', 'logout', 'verify-token', 'verify.2fa'];
            
            if (!$request->routeIs($allowedRoutes) && !$request->is('livewire/*')) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
