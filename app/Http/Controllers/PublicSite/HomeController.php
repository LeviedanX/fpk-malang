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
            ->latestPublished()
            ->when($featuredArticle, fn ($query) => $query->whereKeyNot($featuredArticle->getKey()))
            ->limit(config('fpk.home.latest_articles'))
            ->get();

        $upcomingAgendas = Agenda::query()
            ->upcoming()
            ->limit(config('fpk.home.upcoming_agendas'))
            ->get();

        $activePeriod = ManagementPeriod::query()
            ->active()
            ->with('activeMembers')
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
