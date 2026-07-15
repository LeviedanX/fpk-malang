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
            'agendasTotal' => Agenda::query()->count(),
            'agendasUpcoming' => Agenda::query()->upcoming()->count(),
            'membersTotal' => ManagementMember::query()->count(),
            'latestArticles' => Article::query()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'status', 'published_at', 'created_at']),
            'nearestAgendas' => Agenda::query()
                ->where('publication_status', PublicationStatus::Published)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'starts_at', 'event_status']),
        ]);
    }
}
