<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Article;
use App\Models\ContactSetting;
use App\Models\FpkProfile;
use App\Models\ManagementPeriod;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredArticle = Article::featuredForHome();

        $latestArticles = Article::query()
            ->select([
                'id', 'title', 'slug', 'excerpt', 'thumbnail_path',
                'is_featured', 'status', 'published_at',
            ])
            ->latestPublished()
            ->when($featuredArticle, fn ($query) => $query->whereKeyNot($featuredArticle->getKey()))
            ->limit(config('fpk.home.latest_articles'))
            ->get();

        $upcomingAgendas = Agenda::query()
            ->select([
                'id', 'title', 'slug', 'location', 'starts_at',
                'event_status', 'publication_status', 'published_at',
            ])
            ->upcoming()
            ->limit(config('fpk.home.upcoming_agendas'))
            ->get();

        $activePeriod = ManagementPeriod::query()
            ->active()
            ->with(['activeMembers' => fn ($query) => $query->select([
                'id', 'management_period_id', 'name', 'position', 'division',
                'portrait_path', 'display_order', 'is_active',
            ])])
            ->first();

        return view('public-site.home', [
            'profile' => FpkProfile::current(),
            'contact' => ContactSetting::current(),
            'featuredArticle' => $featuredArticle,
            'latestArticles' => $latestArticles,
            'upcomingAgendas' => $upcomingAgendas,
            'activePeriod' => $activePeriod,
        ]);
    }
}
