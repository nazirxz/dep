<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has the required role
        if (auth()->user()->role !== $role) {
            // Redirect based on user's actual role
            if (auth()->user()->role === 'manager') {
                return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            } elseif (auth()->user()->role === 'admin') {
                return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            } else {
                return redirect()->route('login')->with('error', 'Role tidak dikenali.');
            }
        }

        return $next($request);
    }
}