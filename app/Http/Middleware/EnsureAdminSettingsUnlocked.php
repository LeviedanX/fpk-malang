<?php

namespace App\Http\Middleware;

use App\Support\AdminPinGate;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSettingsUnlocked
{
    public function __construct(private readonly AdminPinGate $pinGate) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if ($this->pinGate->canPerformActions($request)) {
            // Pertahankan tampilan terbuka setelah redirect validasi atau
            // penyimpanan, tanpa membuat akses GET berikutnya permanen terbuka.
            $this->pinGate->grantEntry($request);

            return $next($request);
        }

        return redirect()
            ->route('admin.account.edit')
            ->withErrors(['pin' => 'Masukkan PIN untuk membuka Pengaturan Admin.']);
    }
}
