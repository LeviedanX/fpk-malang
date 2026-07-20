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
            ['Ketua', 'Ahmad Fuad Rahman, S.E., M.M.'],
            ['Wakil Ketua', 'Drs. Hedher Taukia, M.PdI.'],
            ['Sekretaris', 'Dra. Atfiah El Zam Zami, M.M.'],
            ['Wakil Sekretaris', 'Sahran, S.PdI., M.PdI.'],
        ];

        // Each bidang lists [position, name] following the source report's
        // Koordinator, Wakil Koordinator, Anggota, Anggota ordering.
        $divisions = [
            'Bidang Komunikasi dan Jaringan Masyarakat' => [
                ['Koordinator', 'Sani Sinarsana Kisid, S.T., M.T.'],
                ['Wakil Koordinator', 'Tetty Veronika Sormin'],
                ['Anggota', 'Ani Rusidah'],
                ['Anggota', 'Drs. Muarib, M.Si.'],
            ],
            'Bidang Dialog dan Advokasi' => [
                ['Koordinator', 'Dudung Kusnadi, S.Kp., M.Pol.'],
                ['Wakil Koordinator', 'Dra. Selviana Pellokila, M.M.'],
                ['Anggota', 'Surya Azita'],
                ['Anggota', 'Matthew Makuke'],
            ],
            'Bidang Sosialisasi dan Edukasi' => [
                ['Koordinator', 'Meiman Solala Halawa, S.E., M.M.'],
                ['Wakil Koordinator', 'Tru Nur Santy'],
                ['Anggota', "Ja'far Shodiq"],
                ['Anggota', 'Bambang Sukoco, S.E.'],
            ],
            'Bidang Kajian dan Perumusan Kebijakan' => [
                ['Koordinator', 'I Nyoman Sedana, S.H.'],
                ['Wakil Koordinator', 'Dra. Amalia Marzuki'],
                ['Anggota', 'Ir. Yohandri Roza'],
                ['Anggota', 'Risky Noor Hamidinah'],
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
            foreach ($members as [$position, $name]) {
                $period->members()->create([
                    'name' => $name,
                    'position' => $position,
                    'division' => $division,
                    'display_order' => $order += 10,
                    'is_active' => true,
                ]);
            }
        }
    }
}
