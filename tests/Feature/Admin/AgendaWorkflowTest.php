<?php

namespace Tests\Feature\Admin;

use App\Enums\AgendaStatus;
use App\Models\Agenda;
use App\Models\AgendaLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgendaWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_time_based_status_is_normalized_when_agenda_is_saved(): void
    {
        Carbon::setTestNow('2026-07-23 10:00:00');
        $user = User::factory()->create();

        $cases = [
            'Agenda Akan Datang' => [
                'starts_at' => '2026-07-24 09:00:00',
                'ends_at' => '2026-07-24 12:00:00',
                'event_status' => AgendaStatus::Completed->value,
                'expected' => AgendaStatus::Scheduled,
            ],
            'Agenda Sedang Berjalan' => [
                'starts_at' => '2026-07-23 09:00:00',
                'ends_at' => '2026-07-23 12:00:00',
                'event_status' => AgendaStatus::Scheduled->value,
                'expected' => AgendaStatus::Ongoing,
            ],
            'Agenda Sudah Lewat' => [
                'starts_at' => '2026-07-22 09:00:00',
                'ends_at' => '2026-07-22 12:00:00',
                'event_status' => AgendaStatus::Ongoing->value,
                'expected' => AgendaStatus::Completed,
            ],
            'Agenda Dibatalkan' => [
                'starts_at' => '2026-07-24 09:00:00',
                'ends_at' => '2026-07-24 12:00:00',
                'event_status' => AgendaStatus::Cancelled->value,
                'expected' => AgendaStatus::Cancelled,
            ],
            'Agenda Tanpa Waktu Selesai' => [
                'starts_at' => '2026-07-23 09:00:00',
                'ends_at' => null,
                'event_status' => AgendaStatus::Completed->value,
                'expected' => AgendaStatus::Completed,
            ],
        ];

        foreach ($cases as $title => $case) {
            $this->actingAs($user)
                ->post(route('admin.agendas.store'), [
                    'title' => $title,
                    'slug' => '',
                    'starts_at' => $case['starts_at'],
                    'ends_at' => $case['ends_at'],
                    'event_status' => $case['event_status'],
                    'publication_status' => 'draft',
                ])
                ->assertRedirect(route('admin.agendas.index'))
                ->assertSessionDoesntHaveErrors();

            $this->assertSame(
                $case['expected'],
                Agenda::firstWhere('title', $title)->event_status,
                "Status {$title} tidak dinormalisasi sesuai waktu acara.",
            );
        }

        $this->assertSame(count($cases), AgendaLog::query()->where('user_id', $user->id)->where('action', 'created')->count());
    }

    public function test_effective_status_changes_with_time_without_scheduler(): void
    {
        $agenda = Agenda::factory()->make([
            'starts_at' => '2026-07-23 09:00:00',
            'ends_at' => '2026-07-23 12:00:00',
            'event_status' => AgendaStatus::Scheduled,
        ]);

        $this->assertSame(
            AgendaStatus::Scheduled,
            $agenda->effectiveEventStatus(Carbon::parse('2026-07-23 08:59:59')),
        );
        $this->assertSame(
            AgendaStatus::Ongoing,
            $agenda->effectiveEventStatus(Carbon::parse('2026-07-23 10:00:00')),
        );
        $this->assertSame(
            AgendaStatus::Completed,
            $agenda->effectiveEventStatus(Carbon::parse('2026-07-23 12:00:01')),
        );
    }

    public function test_admin_list_displays_the_effective_status_instead_of_stale_database_status(): void
    {
        Carbon::setTestNow('2026-07-23 13:00:00');
        $user = User::factory()->create();

        Agenda::factory()->create([
            'title' => 'Agenda Status Dinamis',
            'starts_at' => '2026-07-23 09:00:00',
            'ends_at' => '2026-07-23 12:00:00',
            'event_status' => AgendaStatus::Scheduled,
        ]);

        $this->actingAs($user)
            ->get(route('admin.agendas.history', ['q' => 'Agenda Status Dinamis']))
            ->assertOk()
            ->assertSeeInOrder(['Agenda Status Dinamis', 'Selesai']);

        $this->actingAs($user)
            ->get(route('admin.agendas.index'))
            ->assertOk()
            ->assertDontSee('Agenda Status Dinamis');
    }

    public function test_public_scopes_group_ongoing_and_historical_agendas_consistently(): void
    {
        Carbon::setTestNow('2026-07-23 10:00:00');

        $ongoing = Agenda::factory()->create([
            'starts_at' => '2026-07-23 09:00:00',
            'ends_at' => '2026-07-23 12:00:00',
            'event_status' => AgendaStatus::Scheduled,
        ]);
        $openEnded = Agenda::factory()->create([
            'starts_at' => '2026-07-23 09:00:00',
            'ends_at' => null,
            'event_status' => AgendaStatus::Scheduled,
        ]);
        $completed = Agenda::factory()->create([
            'starts_at' => '2026-07-22 09:00:00',
            'ends_at' => '2026-07-22 12:00:00',
            'event_status' => AgendaStatus::Scheduled,
        ]);
        $cancelled = Agenda::factory()->create([
            'starts_at' => '2026-07-22 09:00:00',
            'ends_at' => null,
            'event_status' => AgendaStatus::Cancelled,
        ]);

        $this->assertEqualsCanonicalizing(
            [$ongoing->id, $openEnded->id],
            Agenda::query()
                ->whereKey([$ongoing->id, $openEnded->id, $completed->id, $cancelled->id])
                ->upcoming()
                ->pluck('id')
                ->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$completed->id, $cancelled->id],
            Agenda::query()
                ->whereKey([$ongoing->id, $openEnded->id, $completed->id, $cancelled->id])
                ->past()
                ->pluck('id')
                ->all(),
        );
    }

    public function test_end_time_must_be_later_than_start_time(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.agendas.store'), [
                'title' => 'Agenda Waktu Tidak Valid',
                'slug' => '',
                'starts_at' => '2026-07-23 09:00:00',
                'ends_at' => '2026-07-23 09:00:00',
                'event_status' => AgendaStatus::Scheduled->value,
                'publication_status' => 'draft',
            ])
            ->assertSessionHasErrors([
                'ends_at' => 'Waktu selesai harus lebih besar daripada waktu mulai.',
            ]);
    }

    public function test_admin_can_restore_or_permanently_delete_an_archived_agenda(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Storage::disk('public')->put('agendas/arsip.png', 'poster');

        $agenda = Agenda::factory()->create([
            'title' => 'Agenda Arsip',
            'poster_path' => 'agendas/arsip.png',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.agendas.destroy', $agenda))
            ->assertRedirect(route('admin.agendas.index'));

        Storage::disk('public')->assertExists('agendas/arsip.png');
        $this->assertDatabaseHas('agenda_logs', ['agenda_id' => $agenda->id, 'action' => 'archived']);

        $this->actingAs($user)
            ->get(route('admin.agendas.history', ['q' => 'Agenda Arsip']))
            ->assertOk()
            ->assertSee('Agenda Arsip')
            ->assertSee('aktivitas');

        $this->actingAs($user)
            ->get(route('admin.agendas.archive'))
            ->assertOk()
            ->assertSee('Agenda Arsip')
            ->assertSee('Pulihkan')
            ->assertSee('Hapus Permanen');

        $this->actingAs($user)
            ->patch(route('admin.agendas.restore', $agenda))
            ->assertRedirect(route('admin.agendas.archive'));

        $this->assertNotSoftDeleted($agenda);
        $this->assertDatabaseHas('agenda_logs', ['agenda_id' => $agenda->id, 'action' => 'restored']);

        $this->actingAs($user)
            ->get(route('admin.agendas.logs', $agenda))
            ->assertOk()
            ->assertSee('Agenda diarsipkan')
            ->assertSee('Agenda dipulihkan');

        $agenda->delete();

        $this->actingAs($user)
            ->delete(route('admin.agendas.force-delete', $agenda))
            ->assertRedirect(route('admin.agendas.archive'));

        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);
        $this->assertDatabaseMissing('agenda_logs', ['agenda_id' => $agenda->id]);
        Storage::disk('public')->assertMissing('agendas/arsip.png');
    }
}
