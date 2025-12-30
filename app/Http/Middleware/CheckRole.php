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
        if (!auth()->check()) {
            return redirect('/admin/login');
        }

        $user = auth()->user();

        // Check if user has the required role
        if ($user->role !== $role) {
            // Redirect to appropriate panel based on actual role
            if ($user->isAdministrator()) {
                return redirect('/admin');
            } elseif ($user->isGuru()) {
                return redirect('/guru');
            }
            
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
