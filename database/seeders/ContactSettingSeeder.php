<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use Illuminate\Database\Seeder;

/**
 * Creates an empty contact row only. Official address, phone, WhatsApp, email,
 * and social media are intentionally left blank and must be filled by the admin
 * once verified against official sources. Never seed placeholder contact data.
 */
class ContactSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (ContactSetting::query()->where('singleton_key', 1)->exists()) {
            return;
        }

        ContactSetting::create(['singleton_key' => 1]);
    }
}
