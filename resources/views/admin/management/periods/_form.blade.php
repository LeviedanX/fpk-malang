<x-admin.card>
    <div class="space-y-4">
        <x-form.input name="name" label="Nama Periode" :value="$period->name" required hint="Contoh: Periode 2025-2027" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.input name="start_year" label="Tahun Mulai" type="number" :value="$period->start_year" required />
            <x-form.input name="end_year" label="Tahun Berakhir" type="number" :value="$period->end_year" />
        </div>
        <x-form.image-field
            name="group_photo"
            label="Foto Bersama Pengurus"
            :current="$period->group_photo_path"
            hint="Gunakan foto landscape/lebar. Foto ini tampil di bagian atas Susunan Pengurus. Format JPG, PNG, atau WEBP; maksimal 2 MB."
        />
        <x-form.checkbox name="is_active" label="Jadikan periode aktif" :checked="$period->is_active"
            hint="Hanya satu periode yang dapat aktif. Mengaktifkan periode ini akan menonaktifkan periode lain." />
    </div>
</x-admin.card>

<div class="reveal mt-6 grid gap-2 sm:flex">
    <button type="submit" class="admin-button admin-button-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.periods.index') }}" class="admin-button admin-button-secondary">Batal</a>
</div>
