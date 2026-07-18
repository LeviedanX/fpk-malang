<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(): View
    {
        $columns = [
            'id', 'title', 'slug', 'location', 'starts_at',
            'event_status', 'publication_status', 'published_at',
        ];

        $upcoming = Agenda::query()->select($columns)->upcoming()->get();

        $past = Agenda::query()
            ->select($columns)
            ->past()
            ->paginate(9)
            ->withQueryString();

        return view('public-site.agendas.index', [
            'upcoming' => $upcoming,
            'past' => $past,
        ]);
    }

    public function show(Agenda $agenda): View
    {
        abort_unless($agenda->isPublished(), 404);

        return view('public-site.agendas.show', [
            'agenda' => $agenda,
        ]);
    }
}
