<?php

namespace Tests\Feature\Admin;

use App\Models\ManagementPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_activating_a_period_deactivates_others(): void
    {
        $user = User::factory()->create();
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
}
