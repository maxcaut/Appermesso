<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePrivacyConsent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) $request->session()->get('privacy_consent_seen', false)) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
