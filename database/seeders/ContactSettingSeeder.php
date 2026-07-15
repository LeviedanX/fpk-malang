<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use Illuminate\Database\Seeder;

/**
 * Creates an empty contact row only. Official address, phone, WhatsApp, email,
 * and social media are intentionally left blank and must be filled by the admin
 * once verified (see TODO.md). Never seed placeholder contact data.
 */
class ContactSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (ContactSetting::query()->exists()) {
            return;
        }

        ContactSetting::create();
    }
}
