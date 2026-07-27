<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminSessionSecurity;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecureAdminSession
{
    public function __construct(private readonly AdminSessionSecurity $security) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $this->security->isValid($request, $user)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Sesi admin dihentikan karena perubahan perangkat atau masa sesi telah berakhir. Silakan masuk kembali.']);
        }

        $this->security->rotateIfDue($request);

        return $next($request);
    }
}
