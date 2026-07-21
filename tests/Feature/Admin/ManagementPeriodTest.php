<?php

namespace Tests\Feature\Admin;

use App\Models\ManagementPeriod;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagementPeriodTest extends TestCase
{
    use DatabaseTransactions;

    private function pngUpload(string $name = 'pengurus.png'): UploadedFile
    {
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk'
            .'+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    public function test_admin_can_create_period_with_group_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.periods.store'), [
            'name' => 'Periode 2027-2029',
            'start_year' => 2027,
            'end_year' => 2029,
            'group_photo' => $this->pngUpload(),
            'is_active' => '1',
        ])->assertRedirect(route('admin.periods.index'));

        $period = ManagementPeriod::firstWhere('name', 'Periode 2027-2029');

        $this->assertNotNull($period?->group_photo_path);
        Storage::disk('public')->assertExists($period->group_photo_path);
    }

    public function test_activating_a_period_deactivates_others(): void
    {
        $user = User::factory()->create();
        ManagementPeriod::query()->update(['is_active' => false]);
        $existing = ManagementPeriod::factory()->active()->create();
        $new = ManagementPeriod::factory()->create(['is_active' => false]);

        $this->actingAs($user)->put(route('admin.periods.update', $new), [
            'name' => $new->name,
            'start_year' => $new->start_year,
            'end_year' => $new->end_year,
            'is_active' => '1',
        ])->assertRedirect(route('admin.periods.index'));

        $this->assertTrue($new->fresh()->is_active);
        $this->assertFalse($existing->fresh()->is_active, 'Only one period may be active.');
    }

    public function test_end_year_cannot_precede_start_year(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.periods.store'), [
            'name' => 'Periode Salah',
            'start_year' => 2027,
            'end_year' => 2025,
        ])->assertSessionHasErrors('end_year');
    }

    public function test_end_year_is_required_and_must_be_later_than_current_year(): void
    {
        $user = User::factory()->create();
        $startYear = (int) now()->year - 1;

        $this->actingAs($user)->post(route('admin.periods.store'), [
            'name' => 'Tanpa Tahun Selesai',
            'start_year' => $startYear,
            'end_year' => null,
        ])->assertSessionHasErrors('end_year');

        $response = $this->actingAs($user)->post(route('admin.periods.store'), [
            'name' => 'Tahun Selesai Tidak Valid',
            'start_year' => $startYear,
            'end_year' => (int) now()->year,
        ])->assertSessionHasErrors('end_year');

        $this->assertStringContainsString(
            'Tahun selesai harus lebih besar dari tahun saat ini',
            $response->getSession()->get('errors')->first('end_year'),
        );
    }

    public function test_database_rejects_more_than_one_active_period(): void
    {
        ManagementPeriod::query()->update(['is_active' => false]);
        ManagementPeriod::factory()->active()->create();

        $this->expectException(QueryException::class);
        ManagementPeriod::factory()->active()->create();
    }
}
