<x-admin.card>
    <div class="space-y-4">
        <x-form.input name="name" label="Nama Periode" :value="$period->name" required hint="Contoh: Periode 2025-2027" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.input name="start_year" label="Tahun Mulai" type="number" :value="$period->start_year" required />
            <x-form.input name="end_year" label="Tahun Berakhir" type="number" :value="$period->end_year" />
        </div>
        <x-form.checkbox name="is_active" label="Jadikan periode aktif" :checked="$period->is_active"
            hint="Hanya satu periode yang dapat aktif. Mengaktifkan periode ini akan menonaktifkan periode lain." />
    </div>
</x-admin.card>

<div class="mt-6 flex gap-2">
    <button type="submit" class="rounded-md bg-maroon-700 px-6 py-2.5 font-medium text-cream-50 hover:bg-maroon-800">{{ $submitLabel }}</button>
    <a href="{{ route('admin.periods.index') }}" class="rounded-md border border-slate-300 px-6 py-2.5 font-medium text-slate-600 hover:bg-slate-50">Batal</a>
</div>
