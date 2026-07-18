<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgendaRequest;
use App\Models\Agenda;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $agendas = Agenda::query()
            ->select(['id', 'title', 'slug', 'starts_at', 'event_status', 'publication_status'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when(
                in_array($status, [PublicationStatus::Draft->value, PublicationStatus::Published->value], true),
                fn ($query) => $query->where('publication_status', $status)
            )
            ->orderByDesc('starts_at')
            ->paginate(config('fpk.pagination.agendas_admin'))
            ->withQueryString();

        return view('admin.agendas.index', [
            'agendas' => $agendas,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.agendas.create', [
            'agenda' => new Agenda([
                'event_status' => AgendaStatus::Scheduled->value,
                'publication_status' => PublicationStatus::Published->value,
            ]),
        ]);
    }

    public function store(AgendaRequest $request): RedirectResponse
    {
        $data = $this->buildData($request);

        if ($request->hasFile('poster')) {
            $data['poster_path'] = ImageStorage::store($request->file('poster'), 'agendas');
        }

        Agenda::create($data);

        return redirect()
            ->route('admin.agendas.index')
            ->with('status', 'Agenda berhasil dibuat.');
    }

    public function edit(Agenda $agenda): View
    {
        return view('admin.agendas.edit', [
            'agenda' => $agenda,
        ]);
    }

    public function update(AgendaRequest $request, Agenda $agenda): RedirectResponse
    {
        $data = $this->buildData($request, $agenda);

        if ($request->hasFile('poster')) {
            $data['poster_path'] = ImageStorage::replace(
                $request->file('poster'),
                $agenda->poster_path,
                'agendas'
            );
        }

        $agenda->update($data);

        return redirect()
            ->route('admin.agendas.index')
            ->with('status', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $agenda->delete();

        return redirect()
            ->route('admin.agendas.index')
            ->with('status', 'Agenda berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildData(AgendaRequest $request, ?Agenda $agenda = null): array
    {
        $data = $request->safe()->except('poster');

        if ($data['publication_status'] === PublicationStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = $agenda?->published_at ?? now();
        }

        if ($data['publication_status'] === PublicationStatus::Draft->value) {
            $data['published_at'] = $data['published_at'] ?? null;
        }

        return $data;
    }
}
