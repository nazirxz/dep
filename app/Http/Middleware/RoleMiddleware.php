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
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has any of the required roles
        $userRole = auth()->user()->role;
        if (!in_array($userRole, $roles)) {
            // Redirect based on user's actual role
            if ($userRole === 'manager') {
                return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            } elseif ($userRole === 'admin') {
                return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            } else {
                return redirect()->route('login')->with('error', 'Role tidak dikenali.');
            }
        }

        return $next($request);
    }
}