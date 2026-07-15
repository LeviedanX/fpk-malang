<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactSettingRequest;
use App\Models\ContactSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.contact.edit', [
            'contact' => ContactSetting::query()->first() ?? new ContactSetting,
        ]);
    }

    public function update(ContactSettingRequest $request): RedirectResponse
    {
        $contact = ContactSetting::query()->first() ?? new ContactSetting;

        $contact->fill($request->validated())->save();

        return redirect()
            ->route('admin.contact.edit')
            ->with('status', 'Kontak & media sosial berhasil diperbarui.');
    }
}
