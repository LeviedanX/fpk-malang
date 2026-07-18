<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function show(Agenda $agenda): View
    {
        abort_unless($agenda->isPublished(), 404);

        return view('public-site.agendas.show', [
            'agenda' => $agenda,
        ]);
    }
}
