<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Article;
use App\Models\ManagementMember;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'articlesTotal' => Article::query()->count(),
            'articlesPublished' => Article::query()->published()->count(),
            'articlesDraft' => Article::query()->where('status', PublicationStatus::Draft)->count(),
            'agendasActive' => Agenda::query()->currentOrUpcoming()->count(),
            'agendasDraft' => Agenda::query()
                ->currentOrUpcoming()
                ->where('publication_status', PublicationStatus::Draft)
                ->count(),
            'membersTotal' => ManagementMember::query()->count(),
            'latestArticles' => Article::query()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'status', 'published_at', 'created_at']),
            'nearestAgendas' => Agenda::query()
                ->currentOrUpcoming()
                ->orderBy('starts_at')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'starts_at', 'event_status', 'publication_status', 'published_at']),
        ]);
    }
}
