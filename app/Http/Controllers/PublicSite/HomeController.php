<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Article;
use App\Models\ContactSetting;
use App\Models\FpkProfile;
use App\Models\GalleryImage;
use App\Models\ManagementPeriod;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $profile = FpkProfile::current();
        $contact = ContactSetting::current();
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

        $agendaColumns = [
            'id', 'title', 'slug', 'location', 'starts_at', 'ends_at',
            'event_status', 'publication_status', 'published_at',
        ];

        $upcomingAgendas = Agenda::query()
            ->select($agendaColumns)
            ->visibleOnPublic()
            ->limit(4)
            ->get();

        $galleryImages = GalleryImage::query()
            ->visible()
            ->get(['id', 'image_path', 'display_order']);

        $activePeriod = ManagementPeriod::query()
            ->active()
            ->with(['activeMembers' => fn ($query) => $query->select([
                'id', 'management_period_id', 'name', 'position', 'division',
                'portrait_path', 'display_order', 'is_active',
            ])])
            ->first();

        request()->attributes->set('fpk.public_content_visibility', [
            'articles' => $featuredArticle !== null,
            'agendas' => $upcomingAgendas->isNotEmpty(),
            'gallery' => $galleryImages->isNotEmpty(),
            'management' => $activePeriod !== null
                && (filled($activePeriod->group_photo_path) || $activePeriod->activeMembers->isNotEmpty()),
            'contact' => $contact->hasAnyContact() || filled($contact->map_embed_url),
        ]);

        return view('public-site.home', [
            'profile' => $profile,
            'contact' => $contact,
            'featuredArticle' => $featuredArticle,
            'latestArticles' => $latestArticles,
            'upcomingAgendas' => $upcomingAgendas,
            'galleryImages' => $galleryImages,
            'activePeriod' => $activePeriod,
        ]);
    }
}
