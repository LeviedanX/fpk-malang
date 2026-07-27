<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\AdminActivityLogger;
use App\Support\AdminDeviceIdentity;
use App\Support\AdminPinGate;
use App\Support\AdminSessionSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request, AdminDeviceIdentity $deviceIdentity): Response
    {
        $deviceId = (string) $request->cookie(AdminDeviceIdentity::COOKIE_NAME, '');

        if (! Str::isUuid($deviceId)) {
            $deviceId = $deviceIdentity->newId();
        }

        return response()
            ->view('auth.login')
            ->withCookie(Cookie::make(
                AdminDeviceIdentity::COOKIE_NAME,
                $deviceId,
                525600,
                '/',
                null,
                (bool) config('session.secure', false),
                true,
                false,
                'strict',
            ));
    }

    public function store(
        LoginRequest $request,
        AdminSessionSecurity $sessionSecurity,
    ): RedirectResponse {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $sessionSecurity->establish($request, $user);
        app(AdminActivityLogger::class)->log(
            $request,
            'auth.login_success',
            'Admin berhasil masuk.',
            $user,
            302,
        );

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        app(AdminActivityLogger::class)->log(
            $request,
            'auth.logout',
            'Admin keluar dari sistem.',
            $user instanceof User ? $user : null,
            302,
        );
        app(AdminPinGate::class)->revoke($request);
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
