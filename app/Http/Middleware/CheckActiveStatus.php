<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification;

class CheckActiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/admin/login');
        }

        $user = auth()->user();

        // Check if account is active
        if (!$user->isActive()) {
            auth()->logout();
            
            // Show notification about pending approval
            Notification::make()
                ->title('Akun Menunggu Persetujuan')
                ->body('Akun Anda sedang menunggu persetujuan dari administrator. Silakan hubungi administrator untuk informasi lebih lanjut.')
                ->warning()
                ->persistent()
                ->send();

            return redirect('/admin/login');
        }

        return $next($request);
    }
}
