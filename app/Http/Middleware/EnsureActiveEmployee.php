<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveEmployee
{
    /**
     * Ensure the authenticated user's employee profile is active.
     * Inactive employees cannot perform attendance or access employee features.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isEmployee()) {
            $employee = $user->employee;

            if (!$employee || !$employee->isActive()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akun karyawan Anda tidak aktif. Hubungi admin.',
                    ], 403);
                }

                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Akun karyawan Anda tidak aktif. Silakan hubungi admin.');
            }
        }

        return $next($request);
    }
}
