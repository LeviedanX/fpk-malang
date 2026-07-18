<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-admin.card>
            <div class="space-y-4">
                <x-form.input name="title" label="Judul Agenda" :value="$agenda->title" required />
                <x-form.input name="slug" label="Slug" :value="$agenda->slug" hint="Kosongkan untuk dibuat otomatis dari judul." />
                <x-form.textarea name="description" label="Deskripsi" :value="$agenda->description" rows="8" />
                <x-form.input name="location" label="Lokasi" :value="$agenda->location" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input name="starts_at" label="Waktu Mulai" type="datetime-local"
                        :value="old('starts_at', optional($agenda->starts_at)->format('Y-m-d\TH:i'))" required />
                    <x-form.input name="ends_at" label="Waktu Selesai" type="datetime-local"
                        :value="old('ends_at', optional($agenda->ends_at)->format('Y-m-d\TH:i'))" />
                </div>
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Status">
            <div class="space-y-4">
                <x-form.select name="event_status" label="Status Acara" :options="\App\Enums\AgendaStatus::options()" :selected="$agenda->event_status?->value ?? 'scheduled'" required />
                <x-form.select name="publication_status" label="Status Publikasi" :options="\App\Enums\PublicationStatus::options()" :selected="$agenda->publication_status?->value ?? 'published'" required hint="'Terbit' tampil di situs publik; 'Draf' disembunyikan." />
                <x-form.input name="published_at" label="Waktu Terbit" type="datetime-local"
                    :value="old('published_at', optional($agenda->published_at)->format('Y-m-d\TH:i'))"
                    hint="Kosongkan untuk memakai waktu saat ini ketika diterbitkan." />
            </div>
        </x-admin.card>

        <x-admin.card title="Poster">
            <x-form.image-field name="poster" label="Poster Agenda" :current="$agenda->poster_path" />
        </x-admin.card>

        <div class="reveal flex flex-col gap-2">
            <button type="submit" class="admin-button admin-button-primary">{{ $submitLabel }}</button>
            <a href="{{ route('admin.agendas.index') }}" class="admin-button admin-button-secondary">Batal</a>
        </div>
    </div>
</div>
