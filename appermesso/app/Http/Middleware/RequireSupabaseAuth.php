<?php

namespace App\Http\Middleware;

use App\Services\SupabaseSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSupabaseAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app(SupabaseSession::class)->current() === null) {
            return redirect()->route('login')
                ->with('status', 'Accedi per continuare.');
        }

        return $next($request);
    }
}
