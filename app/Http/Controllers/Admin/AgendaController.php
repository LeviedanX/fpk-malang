<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AgendaStatus;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgendaRequest;
use App\Models\Agenda;
use App\Models\AgendaLog;
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
            ->select(['id', 'title', 'slug', 'starts_at', 'ends_at', 'event_status', 'publication_status'])
            ->where(function ($query): void {
                $query
                    ->where('publication_status', PublicationStatus::Draft)
                    ->orWhere(fn ($query) => $query->visibleOnPublic());
            })
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

    public function archive(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $agendas = Agenda::onlyTrashed()
            ->select(['id', 'title', 'slug', 'poster_path', 'starts_at', 'deleted_at'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderByDesc('deleted_at')
            ->paginate(config('fpk.pagination.agendas_admin'))
            ->withQueryString();

        return view('admin.agendas.archive', [
            'agendas' => $agendas,
            'search' => $search,
        ]);
    }

    public function history(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $agendas = Agenda::withTrashed()
            ->withCount('logs')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->where(function ($query): void {
                $query
                    ->whereNotNull('deleted_at')
                    ->orWhere('event_status', AgendaStatus::Cancelled)
                    ->orWhere('ends_at', '<', now())
                    ->orWhere(function ($query): void {
                        $query
                            ->where('starts_at', '<', now())
                            ->whereNull('ends_at')
                            ->whereIn('event_status', [
                                AgendaStatus::Completed,
                                AgendaStatus::Cancelled,
                            ]);
                    });
            })
            ->orderByDesc('starts_at')
            ->paginate(config('fpk.pagination.agendas_admin'))
            ->withQueryString();

        return view('admin.agendas.history', [
            'agendas' => $agendas,
            'search' => $search,
        ]);
    }

    public function logs(string $agenda): View
    {
        $agenda = Agenda::withTrashed()
            ->where('slug', $agenda)
            ->firstOrFail();

        return view('admin.agendas.logs', [
            'agenda' => $agenda,
            'logs' => $agenda->logs()->with('user:id,name')->get(),
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

        $agenda = Agenda::create($data);
        $this->recordLog($agenda, 'created', statusTo: $agenda->event_status?->value);

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
        $statusBefore = $agenda->effectiveEventStatus()->value;
        $data = $this->buildData($request, $agenda);

        if ($request->hasFile('poster')) {
            $data['poster_path'] = ImageStorage::replace(
                $request->file('poster'),
                $agenda->poster_path,
                'agendas'
            );
        }

        $agenda->update($data);
        $this->recordLog(
            $agenda,
            'updated',
            statusFrom: $statusBefore,
            statusTo: $agenda->event_status?->value,
            changes: array_keys($agenda->getChanges()),
        );

        return redirect()
            ->route('admin.agendas.index')
            ->with('status', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $statusBefore = $agenda->effectiveEventStatus()->value;
        $agenda->delete();
        $this->recordLog($agenda, 'archived', statusFrom: $statusBefore);

        return redirect()
            ->route('admin.agendas.index')
            ->with('status', 'Agenda berhasil diarsipkan dan masih dapat dipulihkan.');
    }

    public function restore(string $agenda): RedirectResponse
    {
        $archivedAgenda = $this->findArchived($agenda);
        $statusBefore = $archivedAgenda->event_status?->value;
        $archivedAgenda->restore();
        $this->recordLog($archivedAgenda, 'restored', statusFrom: $statusBefore, statusTo: $archivedAgenda->event_status?->value);

        return redirect()
            ->route('admin.agendas.archive')
            ->with('status', 'Agenda berhasil dipulihkan.');
    }

    public function forceDelete(string $agenda): RedirectResponse
    {
        $archivedAgenda = $this->findForHistory($agenda);
        $posterPath = $archivedAgenda->poster_path;

        $archivedAgenda->forceDelete();
        ImageStorage::delete($posterPath);

        return redirect()
            ->route('admin.agendas.archive')
            ->with('status', 'Agenda dan posternya berhasil dihapus permanen.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildData(AgendaRequest $request, ?Agenda $agenda = null): array
    {
        $data = $request->safe()->except('poster');
        $data['event_status'] = Agenda::statusForSchedule(
            $data['starts_at'],
            $data['ends_at'] ?? null,
            AgendaStatus::from($data['event_status']),
        )->value;

        if ($data['publication_status'] === PublicationStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = $agenda?->published_at ?? now();
        }

        if ($data['publication_status'] === PublicationStatus::Draft->value) {
            $data['published_at'] = $data['published_at'] ?? null;
        }

        return $data;
    }

    private function findArchived(string $slug): Agenda
    {
        return Agenda::onlyTrashed()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function findForHistory(string $slug): Agenda
    {
        return Agenda::withTrashed()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @param  list<string>|null  $changes
     */
    private function recordLog(
        Agenda $agenda,
        string $action,
        ?string $statusFrom = null,
        ?string $statusTo = null,
        ?array $changes = null,
    ): void {
        AgendaLog::create([
            'agenda_id' => $agenda->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'changes' => $changes,
        ]);
    }
}
