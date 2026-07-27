<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\FpkProfile;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function show(Agenda $agenda): View
    {
        abort_unless($agenda->isVisibleOnPublic(), 404);

        return view('public-site.agendas.show', [
            'agenda' => $agenda,
            'profile' => FpkProfile::current(),
        ]);
    }
}
