<?php

namespace Tests\Feature\Admin;

use App\Models\Agenda;
use App\Models\Article;
use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;

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
            route('admin.profile.edit'),
            route('admin.articles.index'),
            route('admin.articles.create'),
            route('admin.articles.edit', $article),
            route('admin.agendas.index'),
            route('admin.agendas.create'),
            route('admin.agendas.edit', $agenda),
            route('admin.periods.index'),
            route('admin.periods.create'),
            route('admin.periods.edit', $period),
            route('admin.members.index'),
            route('admin.members.create'),
            route('admin.members.edit', $member),
            route('admin.contact.edit'),
            route('admin.settings.edit'),
            route('admin.account.edit'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_admin_pages_are_protected_from_guests(): void
    {
        $this->get(route('admin.articles.index'))->assertRedirect(route('login'));
        $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
    }
}
