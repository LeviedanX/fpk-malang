<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountUpdateRequest;
use App\Http\Requests\Admin\AdminPinSetupRequest;
use App\Http\Requests\Admin\AdminPinUpdateRequest;
use App\Http\Requests\Admin\PasswordUpdateRequest;
use App\Models\AdminActivityLog;
use App\Models\AdminPinSecurityState;
use App\Models\User;
use App\Support\AdminPinGate;
use App\Support\AdminSessionSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function edit(Request $request, AdminPinGate $pinGate): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $pinStatus = $pinGate->status($request, $user);
        $isUnlocked = ! $pinStatus['setup_required']
            && ($pinGate->consumeEntryGrant($request)
                || ($request->has('page') && $pinGate->canPerformActions($request)));

        return view('admin.account.edit', [
            'user' => $user,
            'pinStatus' => $pinStatus,
            'isUnlocked' => $isUnlocked,
            'activityLogs' => $isUnlocked
                ? AdminActivityLog::query()->with('user:id,name')->latest()->paginate(25)
                : null,
        ]);
    }

    public function unlock(Request $request, AdminPinGate $pinGate): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if (! is_string($user->admin_pin) || $user->admin_pin === '') {
            return redirect()
                ->route('admin.account.edit')
                ->withErrors(['pin' => 'Buat PIN Pengaturan Admin terlebih dahulu.']);
        }

        $result = $pinGate->verify($request, $user, (string) $request->input('pin'));

        if ($result['valid']) {
            return redirect()->route('admin.account.edit');
        }

        if ($result['locked']) {
            return redirect()
                ->route('admin.account.edit')
                ->withErrors(['pin' => 'Pengaturan Admin dikunci sementara karena terlalu banyak PIN yang salah.']);
        }

        return redirect()
            ->route('admin.account.edit')
            ->withErrors(['pin' => 'PIN admin tidak cocok.']);
    }

    public function setupPin(
        AdminPinSetupRequest $request,
        AdminPinGate $pinGate,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if (is_string($user->admin_pin) && $user->admin_pin !== '') {
            return redirect()
                ->route('admin.account.edit')
                ->withErrors(['pin' => 'PIN admin sudah dibuat. Masukkan PIN untuk membuka pengaturan.']);
        }

        $user->forceFill([
            'admin_pin' => Hash::make($request->validated()['pin']),
        ])->save();
        AdminPinSecurityState::query()->where('user_id', $user->getKey())->delete();
        $pinGate->grant($request);

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'PIN Pengaturan Admin berhasil dibuat.');
    }

    public function updatePin(
        AdminPinUpdateRequest $request,
        AdminPinGate $pinGate,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $verification = $pinGate->verify(
            $request,
            $user,
            $request->validated()['current_pin'],
        );

        if (! $verification['valid']) {
            if ($verification['locked']) {
                $pinGate->revoke($request);
            }

            throw ValidationException::withMessages([
                'current_pin' => $verification['locked']
                    ? 'Penggantian PIN dikunci sementara karena terlalu banyak PIN yang salah.'
                    : 'PIN saat ini tidak cocok.',
            ]);
        }

        $user->forceFill([
            'admin_pin' => Hash::make($request->validated()['pin']),
        ])->save();
        AdminPinSecurityState::query()->where('user_id', $user->getKey())->delete();
        $pinGate->grant($request);

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'PIN Pengaturan Admin berhasil diperbarui.');
    }

    public function clearActivityLogs(Request $request, AdminPinGate $pinGate): RedirectResponse
    {
        AdminActivityLog::query()->delete();
        $pinGate->grantEntry($request);

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Seluruh histori aktivitas admin berhasil dihapus.');
    }

    public function update(
        AccountUpdateRequest $request,
        AdminSessionSecurity $sessionSecurity,
    ): RedirectResponse {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']))->save();
        $sessionSecurity->refreshAfterCredentialChange($request, $user);
        $pinGate = app(AdminPinGate::class);
        $pinGate->grant($request);

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Profil akun berhasil diperbarui.');
    }

    public function updatePassword(
        PasswordUpdateRequest $request,
        AdminSessionSecurity $sessionSecurity,
    ): RedirectResponse {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated()['password']),
        ]);
        $sessionSecurity->refreshAfterCredentialChange($request, $user);
        $pinGate = app(AdminPinGate::class);
        $pinGate->grant($request);

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Password berhasil diperbarui.');
    }
}
