<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\ContactSetting;
use App\Models\FpkProfile;
use App\Models\ManagementPeriod;
use App\Models\SiteSetting;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::query()->first() ?? new SiteSetting,
            'profile' => FpkProfile::query()->first() ?? new FpkProfile,
            'contact' => ContactSetting::query()->first() ?? new ContactSetting,
            'activePeriod' => ManagementPeriod::query()->active()->first(),
        ]);
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $settings = SiteSetting::query()->first() ?? new SiteSetting;
        $profile = FpkProfile::query()->first() ?? new FpkProfile;
        $contact = ContactSetting::query()->first() ?? new ContactSetting;
        $validated = $request->validated();

        $siteData = Arr::only($validated, [
            'site_name',
            'organization_name',
            'abbreviation',
            'tagline',
            'footer_text',
            'default_meta_title',
            'default_meta_description',
            'default_meta_keywords',
        ]);

        $imageMap = [
            'logo' => ['column' => 'logo_path', 'dir' => 'branding'],
            'favicon' => ['column' => 'favicon_path', 'dir' => 'branding'],
            'default_og_image' => ['column' => 'default_og_image_path', 'dir' => 'branding'],
        ];

        foreach ($imageMap as $field => $meta) {
            if ($request->hasFile($field)) {
                $siteData[$meta['column']] = ImageStorage::replace(
                    $request->file($field),
                    $settings->{$meta['column']},
                    $meta['dir']
                );
            }
        }

        $profileData = Arr::only($validated, [
            'hero_title',
            'hero_subtitle',
            'institution_legal_basis',
            'institution_foundation',
            'definition',
            'background',
            'objectives',
            'core_tasks',
            'legal_basis',
        ]);

        $profileImageMap = [
            'hero_image' => ['column' => 'hero_image_path', 'dir' => 'profile'],
            'about_image' => ['column' => 'about_image_path', 'dir' => 'profile'],
        ];

        foreach ($profileImageMap as $field => $meta) {
            if ($request->hasFile($field)) {
                $profileData[$meta['column']] = ImageStorage::replace(
                    $request->file($field),
                    $profile->{$meta['column']},
                    $meta['dir']
                );
            }
        }

        $contactData = Arr::only($validated, [
            'address',
            'phone',
            'whatsapp',
            'email',
            'operational_hours',
            'map_embed_url',
            'instagram_url',
            'facebook_url',
            'youtube_url',
            'tiktok_url',
        ]);

        DB::transaction(function () use ($settings, $siteData, $profile, $profileData, $contact, $contactData): void {
            $settings->fill($siteData)->save();
            $profile->fill($profileData)->save();
            $contact->fill($contactData)->save();
        });

        $section = $validated['settings_section'] ?? 'identitas';

        return redirect()
            ->to(route('admin.settings.edit').'#'.$section)
            ->with('status', 'Pengaturan website berhasil diperbarui.');
    }
}
