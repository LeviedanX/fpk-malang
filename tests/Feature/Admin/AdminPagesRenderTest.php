<?php

namespace Tests\Feature\Admin;

use App\Models\Agenda;
use App\Models\Article;
use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_all_admin_pages_render_for_authenticated_admin(): void
    {
        $user = $this->admin();
        $article = Article::factory()->create();
        $agenda = Agenda::factory()->create();
        $period = ManagementPeriod::factory()->create();
        $member = ManagementMember::factory()->for($period, 'period')->create();

        $urls = [
            route('admin.dashboard'),
            route('admin.articles.index'),
            route('admin.articles.create'),
            route('admin.articles.edit', $article),
            route('admin.agendas.index'),
            route('admin.agendas.history'),
            route('admin.agendas.logs', $agenda),
            route('admin.agendas.archive'),
            route('admin.agendas.create'),
            route('admin.agendas.edit', $agenda),
            route('admin.galleries.index'),
            route('admin.periods.index'),
            route('admin.periods.create'),
            route('admin.periods.edit', $period),
            route('admin.members.index'),
            route('admin.members.create'),
            route('admin.members.edit', $member),
            route('admin.settings.edit'),
            route('admin.account.edit'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertRedirect(route('admin.settings.edit').'#tentang');

        $this->actingAs($user)
            ->get(route('admin.contact.edit'))
            ->assertRedirect(route('admin.settings.edit').'#kontak');

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Identitas &amp; Branding', false)
            ->assertSee('Hero Beranda')
            ->assertSee('Tentang FPK')
            ->assertSee('Kontak &amp; Media', false)
            ->assertSee('SEO')
            ->assertDontSee('>Profil FPK</span>', false)
            ->assertDontSee('>Kontak &amp; Media Sosial</span>', false);
    }

    public function test_admin_pages_are_protected_from_guests(): void
    {
        $this->get(route('admin.articles.index'))->assertRedirect(route('login'));
        $this->get(route('admin.articles.archive'))->assertRedirect(route('login'));
        $this->get(route('admin.agendas.archive'))->assertRedirect(route('login'));
        $this->get(route('admin.agendas.history'))->assertRedirect(route('login'));
        $this->get(route('admin.agendas.logs', ['agenda' => 'agenda-guest-test']))->assertRedirect(route('login'));
        $this->get(route('admin.galleries.index'))->assertRedirect(route('login'));
        $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
    }

    public function test_dashboard_does_not_render_the_redundant_upcoming_agenda_stat(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Agenda')
            ->assertSee('Agenda Terdekat')
            ->assertSee('Status publikasi')
            ->assertSee('Lihat semua')
            ->assertDontSee('Agenda Mendatang');
    }

    public function test_all_admin_image_uploads_render_the_shared_preview(): void
    {
        $user = $this->admin();
        $period = ManagementPeriod::factory()->create();

        $pages = [
            route('admin.settings.edit') => ['logo', 'favicon', 'default_og_image', 'admin_login_background', 'hero_background', 'hero_mobile_background', 'about_image'],
            route('admin.articles.create') => ['thumbnail'],
            route('admin.agendas.create') => ['poster'],
            route('admin.periods.create') => ['group_photo'],
            route('admin.members.create') => ['portrait'],
            route('admin.members.index', ['period' => $period]) => ['group_photo'],
        ];

        foreach ($pages as $url => $fieldNames) {
            $response = $this->actingAs($user)->get($url)->assertOk();

            foreach ($fieldNames as $fieldName) {
                $response->assertSee('data-image-preview-field="'.$fieldName.'"', false);
            }
        }

        $this->actingAs($user)
            ->get(route('admin.galleries.index'))
            ->assertOk()
            ->assertSee('data-multi-image-preview-field="images"', false);

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertSee(asset('assets/images/branding/logo-fpk.webp'), false)
            ->assertSee(asset('assets/images/about/about-fpk-vector.webp'), false)
            ->assertSee('Konten Utama Hero')
            ->assertSee('Tampilan Hero Responsif')
            ->assertSee('Panel Informasi Hero')
            ->assertSee('Tampilan Login Administrator')
            ->assertSee('name="admin_login_background"', false)
            ->assertSee('name="hero_eyebrow"', false)
            ->assertSee('name="hero_primary_cta_label"', false)
            ->assertSee('name="hero_legal_basis_label"', false)
            ->assertSee('name="hero_mobile_background"', false)
            ->assertDontSee('data-image-preview-field="hero_image"', false)
            ->assertDontSee('hero-card-bg.webp', false);
    }

    public function test_member_list_displays_sequential_numbers_instead_of_internal_order_weights(): void
    {
        $user = $this->admin();
        $period = ManagementPeriod::factory()->create();

        ManagementMember::factory()->for($period, 'period')->create([
            'name' => 'Anggota Pertama',
            'display_order' => 10,
        ]);

        ManagementMember::factory()->for($period, 'period')->create([
            'name' => 'Anggota Kedua',
            'display_order' => 20,
        ]);

        $this->actingAs($user)
            ->get(route('admin.members.index', ['period' => $period]))
            ->assertOk()
            ->assertSeeInOrder([
                'data-label="No." class="px-4 py-3 text-slate-500">1</td>',
                'Anggota Pertama',
                'data-label="No." class="px-4 py-3 text-slate-500">2</td>',
                'Anggota Kedua',
            ], false);
    }
}
