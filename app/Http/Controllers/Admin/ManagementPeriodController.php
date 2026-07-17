<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagementPeriodRequest;
use App\Models\ManagementPeriod;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManagementPeriodController extends Controller
{
    public function index(): View
    {
        return view('admin.management.periods.index', [
            'periods' => ManagementPeriod::query()
                ->withCount('members')
                ->orderByDesc('start_year')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.management.periods.create', [
            'period' => new ManagementPeriod,
        ]);
    }

    public function store(ManagementPeriodRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('group_photo');

        if ($request->hasFile('group_photo')) {
            $data['group_photo_path'] = ImageStorage::store($request->file('group_photo'), 'management');
        }

        $this->persist(new ManagementPeriod, $data);

        return redirect()
            ->route('admin.periods.index')
            ->with('status', 'Periode berhasil dibuat.');
    }

    public function edit(ManagementPeriod $period): View
    {
        return view('admin.management.periods.edit', [
            'period' => $period,
        ]);
    }

    public function update(ManagementPeriodRequest $request, ManagementPeriod $period): RedirectResponse
    {
        $data = $request->safe()->except('group_photo');

        if ($request->hasFile('group_photo')) {
            $data['group_photo_path'] = ImageStorage::replace(
                $request->file('group_photo'),
                $period->group_photo_path,
                'management'
            );
        }

        $this->persist($period, $data);

        return redirect()
            ->route('admin.periods.index')
            ->with('status', 'Periode berhasil diperbarui.');
    }

    public function destroy(ManagementPeriod $period): RedirectResponse
    {
        $portraitPaths = $period->members()
            ->whereNotNull('portrait_path')
            ->pluck('portrait_path');

        $period->delete();

        ImageStorage::delete($period->group_photo_path);
        $portraitPaths->each(fn (string $path) => ImageStorage::delete($path));

        return redirect()
            ->route('admin.periods.index')
            ->with('status', 'Periode berhasil dihapus.');
    }

    /**
     * Save a period, enforcing that at most one period is active at a time.
     *
     * @param  array<string, mixed>  $data
     */
    private function persist(ManagementPeriod $period, array $data): void
    {
        DB::transaction(function () use ($period, $data): void {
            $period->fill($data)->save();

            if ($period->is_active) {
                ManagementPeriod::query()
                    ->whereKeyNot($period->getKey())
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }
}
