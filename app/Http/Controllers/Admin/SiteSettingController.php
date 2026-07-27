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
use Illuminate\Support\Facades\Storage;
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
            'background_music_visible',
            'background_music_default_playing',
            'background_music_volume',
        ]);

        $imageMap = [
            'logo' => ['column' => 'logo_path', 'dir' => 'branding'],
            'favicon' => ['column' => 'favicon_path', 'dir' => 'branding'],
            'default_og_image' => ['column' => 'default_og_image_path', 'dir' => 'branding'],
            'admin_login_background' => ['column' => 'admin_login_background_path', 'dir' => 'auth'],
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

        if (! $request->hasFile('admin_login_background')
            && $request->boolean('remove_admin_login_background')) {
            ImageStorage::delete($settings->admin_login_background_path);
            $siteData['admin_login_background_path'] = null;
        }

        if ($request->hasFile('background_music')) {
            if ($settings->background_music_path) {
                Storage::disk('public')->delete($settings->background_music_path);
            }
            $siteData['background_music_path'] = $request->file('background_music')
                ->store(config('fpk.uploads.directories.audio'), 'public');
        } elseif ($request->boolean('remove_background_music') && $settings->background_music_path) {
            Storage::disk('public')->delete($settings->background_music_path);
            $siteData['background_music_path'] = null;
        }

        $pendingSettings = clone $settings;
        $pendingSettings->fill($siteData);

        if ($pendingSettings->isDirty([
            'background_music_path',
            'background_music_visible',
            'background_music_default_playing',
            'background_music_volume',
        ])) {
            $siteData['background_music_preference_version'] =
                max(1, ((int) $settings->background_music_preference_version) + 1);
        }

        $profileData = Arr::only($validated, [
            'hero_eyebrow',
            'hero_title',
            'hero_subtitle',
            'hero_primary_cta_label',
            'hero_secondary_cta_label',
            'hero_legal_basis_label',
            'hero_foundation_label',
            'hero_period_label',
            'institution_legal_basis',
            'institution_foundation',
            'definition',
            'background',
            'objectives',
            'core_tasks',
            'legal_basis',
        ]);

        $profileImageMap = [
            'hero_background' => ['column' => 'hero_background_path', 'dir' => 'profile'],
            'hero_mobile_background' => ['column' => 'hero_mobile_background_path', 'dir' => 'profile'],
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

        if (! $request->hasFile('hero_background') && $request->boolean('remove_hero_background')) {
            ImageStorage::delete($profile->hero_background_path);
            $profileData['hero_background_path'] = null;
        }

        if (! $request->hasFile('hero_mobile_background') && $request->boolean('remove_hero_mobile_background')) {
            ImageStorage::delete($profile->hero_mobile_background_path);
            $profileData['hero_mobile_background_path'] = null;
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
