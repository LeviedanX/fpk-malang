<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagementMemberRequest;
use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagementMemberController extends Controller
{
    public function index(Request $request): View
    {
        $periodId = $request->query('period');

        $members = ManagementMember::query()
            ->with('period:id,name')
            ->when($periodId, fn ($query) => $query->where('management_period_id', $periodId))
            ->orderBy('management_period_id')
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        $periods = ManagementPeriod::query()->orderByDesc('start_year')->get();

        // Foto bersama dikelola untuk periode terpilih; jika tidak ada filter,
        // gunakan periode aktif, atau periode terbaru sebagai cadangan.
        $photoPeriod = $periodId
            ? $periods->firstWhere('id', (int) $periodId)
            : ($periods->firstWhere('is_active', true) ?? $periods->first());

        return view('admin.management.members.index', [
            'members' => $members,
            'periods' => $periods,
            'periodId' => $periodId,
            'photoPeriod' => $photoPeriod,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.management.members.create', [
            'member' => new ManagementMember([
                'management_period_id' => $request->query('period'),
                'is_active' => true,
                'display_order' => 0,
            ]),
            'periods' => ManagementPeriod::query()->orderByDesc('start_year')->get(),
        ]);
    }

    public function store(ManagementMemberRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('portrait');

        if ($request->hasFile('portrait')) {
            $data['portrait_path'] = ImageStorage::store($request->file('portrait'), 'management');
        }

        ManagementMember::create($data);

        return redirect()
            ->route('admin.members.index', ['period' => $data['management_period_id']])
            ->with('status', 'Anggota berhasil ditambahkan.');
    }

    public function edit(ManagementMember $member): View
    {
        return view('admin.management.members.edit', [
            'member' => $member,
            'periods' => ManagementPeriod::query()->orderByDesc('start_year')->get(),
        ]);
    }

    public function update(ManagementMemberRequest $request, ManagementMember $member): RedirectResponse
    {
        $data = $request->safe()->except('portrait');

        if ($request->hasFile('portrait')) {
            $data['portrait_path'] = ImageStorage::replace(
                $request->file('portrait'),
                $member->portrait_path,
                'management'
            );
        }

        $member->update($data);

        return redirect()
            ->route('admin.members.index', ['period' => $member->management_period_id])
            ->with('status', 'Anggota berhasil diperbarui.');
    }

    public function destroy(ManagementMember $member): RedirectResponse
    {
        $periodId = $member->management_period_id;

        ImageStorage::delete($member->portrait_path);
        $member->delete();

        return redirect()
            ->route('admin.members.index', ['period' => $periodId])
            ->with('status', 'Anggota berhasil dihapus.');
    }
}
