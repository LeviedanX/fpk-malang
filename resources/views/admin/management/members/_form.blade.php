<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-admin.card>
            <div class="space-y-4">
                <x-form.select name="management_period_id" label="Periode"
                    :options="$periods->pluck('name', 'id')->all()"
                    :selected="$member->management_period_id"
                    placeholder="Pilih periode" required />
                <x-form.input name="name" label="Nama Lengkap" :value="$member->name" required hint="Sertakan gelar bila ada. Verifikasi ejaan sebelum dipublikasikan." />
                <x-form.input name="position" label="Jabatan" :value="$member->position" required hint="Contoh: Ketua, Sekretaris, Anggota." />
                <x-form.input name="division" label="Bidang" :value="$member->division" hint="Contoh: Pengurus Inti, Bidang Dialog dan Advokasi." />
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Tampilan">
            <div class="space-y-4">
                <x-form.input name="display_order" label="Urutan" type="number" :value="$member->display_order ?? 0" required hint="Angka lebih kecil tampil lebih dahulu." />
                <x-form.checkbox name="is_active" label="Tampilkan di situs" :checked="$member->is_active ?? true" />
            </div>
        </x-admin.card>

        <x-admin.card title="Foto">
            <x-form.image-field name="portrait" label="Foto Pengurus" :current="$member->portrait_path" />
        </x-admin.card>

        <div class="flex flex-col gap-2">
            <button type="submit" class="rounded-md bg-maroon-700 px-4 py-2.5 font-medium text-cream-50 hover:bg-maroon-800">{{ $submitLabel }}</button>
            <a href="{{ route('admin.members.index') }}" class="rounded-md border border-slate-300 px-4 py-2.5 text-center font-medium text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </div>
</div>
