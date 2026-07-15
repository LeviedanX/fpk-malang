<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::query()->first() ?? new SiteSetting,
        ]);
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $settings = SiteSetting::query()->first() ?? new SiteSetting;

        $data = $request->safe()->except(['logo', 'favicon', 'default_og_image']);

        $imageMap = [
            'logo' => ['column' => 'logo_path', 'dir' => 'branding'],
            'favicon' => ['column' => 'favicon_path', 'dir' => 'branding'],
            'default_og_image' => ['column' => 'default_og_image_path', 'dir' => 'branding'],
        ];

        foreach ($imageMap as $field => $meta) {
            if ($request->hasFile($field)) {
                $data[$meta['column']] = ImageStorage::replace(
                    $request->file($field),
                    $settings->{$meta['column']},
                    $meta['dir']
                );
            }
        }

        $settings->fill($data)->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan website berhasil diperbarui.');
    }
}
