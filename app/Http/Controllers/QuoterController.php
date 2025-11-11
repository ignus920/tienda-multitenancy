<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class QuoterController extends Controller
{
    public function index(Request $request)
    {
        $agent = new Agent();

        // Detectar si es móvil o tablet
        if ($agent->isMobile() || $agent->isTablet()) {
            return redirect()->route('tenant.quoter.mobile');
        }

        // Desktop - redirigir a la ruta desktop
        return redirect()->route('tenant.quoter.desktop');
    }
}