<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();

        if (!$user) {
            $panelPath = $role === 'administrator' ? 'admin' : 'guru';
            return redirect("/{$panelPath}/login");
        }

        if ($user->role !== $role) {
            if ($user->isAdministrator()) {
                return redirect('/admin');
            }
            
            if ($user->isGuru()) {
                return redirect('/guru');
            }

            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
