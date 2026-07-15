<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountUpdateRequest;
use App\Http\Requests\Admin\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function edit(): View
    {
        return view('admin.account.edit');
    }

    public function update(AccountUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated())->save();

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Profil akun berhasil diperbarui.');
    }

    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Password berhasil diperbarui.');
    }
}
