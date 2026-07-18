@php
    // Pilihan tahun kalender (menurun), selalu menyertakan nilai yang tersimpan
    // agar periode lama tetap terpilih walau di luar rentang bawaan.
    $currentYear = (int) now()->year;
    $years = range($currentYear + 5, 2000);

    foreach ([$period->start_year, $period->end_year] as $existingYear) {
        if ($existingYear && ! in_array((int) $existingYear, $years, true)) {
            $years[] = (int) $existingYear;
        }
    }

    rsort($years);
    $yearOptions = array_combine($years, $years);
@endphp

<x-admin.card>
    <div class="space-y-4">
        <x-form.input name="name" label="Nama Periode" :value="$period->name" required hint="Contoh: Periode 2025-2027" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.select name="start_year" label="Tahun Mulai" :options="$yearOptions"
                :selected="$period->start_year ?? $currentYear" required
                hint="Pilih tahun mulai masa bakti." />
            <x-form.select name="end_year" label="Tahun Berakhir" :options="$yearOptions"
                :selected="$period->end_year" placeholder="— Belum ditentukan (opsional)"
                hint="Kosongkan bila masa bakti masih berjalan." />
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
