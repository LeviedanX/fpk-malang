<?php

namespace Database\Seeders;

use App\Models\ManagementPeriod;
use Illuminate\Database\Seeder;

/**
 * Seeds the 2025-2027 management structure as read from CONTENT_REFERENCE.md.
 *
 * IMPORTANT: the period is created INACTIVE so this unverified data is NOT shown
 * publicly by default. Every name, title, spelling, position, and photo must be
 * verified against official documents before the admin activates this period
 * (see TODO.md, Phase 12/14).
 */
class ManagementSeeder extends Seeder
{
    public function run(): void
    {
        if (ManagementPeriod::query()->exists()) {
            return;
        }

        $period = ManagementPeriod::create([
            'name' => 'Periode 2025-2027',
            'start_year' => 2025,
            'end_year' => 2027,
            'is_active' => false, // draft: verify before publishing
        ]);

        $core = [
            ['Ketua', 'Ahmad Fuad Rahman, SE, MM'],
            ['Wakil Ketua', 'Drs. Hedher Taukia, M.Pdl'],
            ['Sekretaris', 'Dra. Atfiah El Zam Zami, MM'],
            ['Wakil Sekretaris', 'Sahran, S.Pdl, M.Pdl'],
        ];

        $divisions = [
            'Bidang Komunikasi dan Jaringan Masyarakat' => [
                'Sani Sinarsana Kisid, ST, MT',
                'Tetty Veronika Sormin',
                'Ani Rusidah',
                'Drs. Muarib, M.Si',
            ],
            'Bidang Dialog dan Advokasi' => [
                'Dudung Kusnadi, S.Kp, M.Pol',
                'Dra. Selviana Pellokila, MM',
                'Surya Azita',
                'Matthew Makuke',
            ],
            'Bidang Sosialisasi dan Edukasi' => [
                'Meiman Solala Halawa, SE, MM',
                'Tru Nur Santy',
                "Ja'far Shodiq",
                'Bambang Sukoco, SE',
            ],
            'Bidang Kajian dan Perumusan Kebijakan' => [
                'I Nyoman Sedana, SH',
                'Dra. Amalia Marzuki',
                'Ir. Yohandri Roza',
                'Risky Noor Hamidinah',
            ],
        ];

        $order = 0;

        foreach ($core as [$position, $name]) {
            $period->members()->create([
                'name' => $name,
                'position' => $position,
                'division' => 'Pengurus Inti',
                'display_order' => $order += 10,
                'is_active' => true,
            ]);
        }

        foreach ($divisions as $division => $members) {
            foreach ($members as $name) {
                $period->members()->create([
                    'name' => $name,
                    'position' => 'Anggota',
                    'division' => $division,
                    'display_order' => $order += 10,
                    'is_active' => true,
                ]);
            }
        }
    }
}
