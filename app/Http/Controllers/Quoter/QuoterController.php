<?php

namespace App\Http\Controllers\Quoter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class QuoterController extends Controller
{
    public function index(Request $request)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        // Detectar si es móvil o tablet
        if ($agent->isMobile() || $agent->isTablet()) {
            return redirect()->route('tenant.quoter.mobile');
        }

        // Desktop - redirigir a la ruta desktop
        return redirect()->route('tenant.quoter.desktop');
    }

    public function mobile(Request $request)
    {
        return view('livewire.tenant.quoter.quoter-mobile');
    }

    public function desktop(Request $request)
    {
        return view('livewire.tenant.quoter.quoter-desktop');
    }

    public function products(Request $request)
    {
        $agent = new Agent();
        $userAgent = $request->header('User-Agent');
        $agent->setUserAgent($userAgent);

        $isMobile = $agent->isMobile();
        $isTablet = $agent->isTablet();

        Log::info('📱 Intento de detección de dispositivo en products()', [
            'userAgent' => $userAgent,
            'isMobile' => $isMobile,
            'isTablet' => $isTablet,
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
            'device' => $agent->device()
        ]);

        // Detectar si es móvil o tablet
        if ($isMobile || $isTablet) {
            return redirect()->route('tenant.quoter.products.mobile');
        }

        // Desktop - redirigir a la ruta desktop
        return redirect()->route('tenant.quoter.products.desktop');
    }

    /**
     * Punto de entrada para la sección de Bodega.
     * Realiza la detección de dispositivo y redirige a la vista correspondiente.
     */
    public function bodega(Request $request)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        if ($agent->isMobile() || $agent->isTablet()) {
            return redirect()->route('tenant.bodega.mobile');
        }

        return redirect()->route('tenant.bodega.desktop');
    }
}
