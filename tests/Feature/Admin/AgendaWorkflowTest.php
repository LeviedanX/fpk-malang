<?php

namespace Tests\Feature\Admin;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use App\Models\Agenda;
use App\Models\AgendaLog;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function test_admin_public_and_database_agenda_classification_stays_consistent(): void
    {
        Carbon::setTestNow('2026-07-27 10:00:00');
        $user = User::factory()->create();

        $scheduledPublication = Agenda::factory()->create([
            'title' => 'Agenda Terbit Terjadwal',
            'starts_at' => '2026-07-27 09:00:00',
            'ends_at' => '2026-07-27 12:00:00',
            'event_status' => AgendaStatus::Ongoing,
            'publication_status' => PublicationStatus::Published,
            'published_at' => '2026-07-27 11:00:00',
        ]);
        $publicAgenda = Agenda::factory()->create([
            'title' => 'Agenda Publik Aktif',
            'starts_at' => '2026-07-28 09:00:00',
            'ends_at' => '2026-07-28 12:00:00',
            'published_at' => '2026-07-26 10:00:00',
        ]);
        $historicalDraft = Agenda::factory()->draft()->create([
            'title' => 'Agenda Draf Selesai',
            'starts_at' => '2026-07-26 09:00:00',
            'ends_at' => '2026-07-26 12:00:00',
            'event_status' => AgendaStatus::Completed,
        ]);
        $archivedAgenda = Agenda::factory()->draft()->create([
            'title' => 'Agenda Terarsip',
            'starts_at' => '2026-07-29 09:00:00',
            'ends_at' => '2026-07-29 12:00:00',
        ]);
        $archivedAgenda->delete();

        $this->assertEqualsCanonicalizing(
            [$scheduledPublication->id, $publicAgenda->id],
            Agenda::query()->currentOrUpcoming()->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$historicalDraft->id, $archivedAgenda->id],
            Agenda::withTrashed()->historical()->pluck('id')->all(),
        );
        $this->assertSame(
            [$publicAgenda->id],
            Agenda::query()->visibleOnPublic()->pluck('id')->all(),
        );

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('agendasActive', 2)
            ->assertSee('Agenda Aktif')
            ->assertSee('Agenda Terbit Terjadwal');

        $this->actingAs($user)
            ->get(route('admin.agendas.index'))
            ->assertOk()
            ->assertSee('Agenda Terbit Terjadwal')
            ->assertSee('Terbit terjadwal')
            ->assertSee('Agenda Publik Aktif')
            ->assertDontSee('Agenda Draf Selesai')
            ->assertDontSee('Agenda Terarsip');

        $this->actingAs($user)
            ->get(route('admin.agendas.history'))
            ->assertOk()
            ->assertSee('Agenda Draf Selesai')
            ->assertSee('Agenda Terarsip')
            ->assertDontSee('Agenda Terbit Terjadwal')
            ->assertDontSee('Agenda Publik Aktif');

        $this->actingAs($user)
            ->get(route('admin.agendas.archive'))
            ->assertOk()
            ->assertSee('Agenda Terarsip')
            ->assertDontSee('Agenda Draf Selesai');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Agenda Publik Aktif')
            ->assertDontSee('Agenda Terbit Terjadwal')
            ->assertDontSee('Agenda Draf Selesai')
            ->assertDontSee('Agenda Terarsip');
        $this->get(route('agendas.show', $scheduledPublication))->assertNotFound();
        $this->get(route('agendas.show', $publicAgenda))->assertOk();
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('agendas.show', $publicAgenda), false)
            ->assertDontSee(route('agendas.show', $scheduledPublication), false);
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

    public function test_publication_time_cannot_be_later_than_agenda_end(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.agendas.store'), [
                'title' => 'Agenda Jadwal Terbit Tidak Valid',
                'slug' => '',
                'starts_at' => '2026-07-27 09:00:00',
                'ends_at' => '2026-07-27 12:00:00',
                'event_status' => AgendaStatus::Scheduled->value,
                'publication_status' => PublicationStatus::Published->value,
                'published_at' => '2026-07-27 12:01:00',
            ])
            ->assertSessionHasErrors([
                'published_at' => 'Waktu terbit harus sebelum atau tepat saat agenda selesai.',
            ]);
    }

    public function test_draft_agenda_always_clears_publication_time(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.agendas.store'), [
                'title' => 'Agenda Draf Bersih',
                'slug' => '',
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDays(2),
                'event_status' => AgendaStatus::Scheduled->value,
                'publication_status' => PublicationStatus::Draft->value,
                'published_at' => now()->subDay(),
            ])
            ->assertRedirect(route('admin.agendas.index'))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('agendas', [
            'title' => 'Agenda Draf Bersih',
            'publication_status' => PublicationStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function test_database_rejects_invalid_agenda_publication_state(): void
    {
        $this->expectException(QueryException::class);

        DB::table('agendas')->insert([
            'title' => 'Agenda Database Tidak Valid',
            'slug' => 'agenda-database-tidak-valid',
            'starts_at' => '2026-07-27 09:00:00',
            'ends_at' => '2026-07-27 12:00:00',
            'event_status' => AgendaStatus::Scheduled->value,
            'publication_status' => PublicationStatus::Published->value,
            'published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
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

    public function test_active_agenda_cannot_be_force_deleted_through_history_endpoint(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'event_status' => AgendaStatus::Scheduled,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.agendas.force-delete', $agenda))
            ->assertNotFound();

        $this->assertDatabaseHas('agendas', ['id' => $agenda->id, 'deleted_at' => null]);
    }
}
