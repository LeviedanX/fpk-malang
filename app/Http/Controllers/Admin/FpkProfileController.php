<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FpkProfileRequest;
use App\Models\FpkProfile;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FpkProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit', [
            'profile' => FpkProfile::query()->first() ?? new FpkProfile,
        ]);
    }

    public function update(FpkProfileRequest $request): RedirectResponse
    {
        $profile = FpkProfile::query()->first() ?? new FpkProfile;

        $data = $request->safe()->except('hero_image');

        if ($request->hasFile('hero_image')) {
            $data['hero_image_path'] = ImageStorage::replace(
                $request->file('hero_image'),
                $profile->hero_image_path,
                'profile'
            );
        }

        $profile->fill($data)->save();

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Profil FPK berhasil diperbarui.');
    }
}
