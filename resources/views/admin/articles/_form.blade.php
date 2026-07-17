<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-admin.card>
            <div class="space-y-4">
                <x-form.input name="title" label="Judul Artikel" :value="$article->title" required />
                <x-form.input name="slug" label="Slug" :value="$article->slug" hint="Kosongkan untuk dibuat otomatis dari judul." />
                <x-form.textarea name="excerpt" label="Ringkasan" :value="$article->excerpt" rows="3" hint="Ringkasan singkat untuk daftar artikel (opsional)." />
                <x-form.rich-text name="body" label="Isi Artikel" :value="$article->body" required />
            </div>
        </x-admin.card>

        <x-admin.card title="SEO" description="Opsional. Digunakan pada mesin pencari dan berbagi tautan.">
            <div class="space-y-4">
                <x-form.input name="meta_title" label="Meta Title" :value="$article->meta_title" />
                <x-form.textarea name="meta_description" label="Meta Description" :value="$article->meta_description" rows="2" />
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Publikasi">
            <div class="space-y-4">
                <x-form.select name="status" label="Status" :options="\App\Enums\PublicationStatus::options()" :selected="$article->status?->value ?? 'draft'" required />
                <x-form.input
                    name="published_at"
                    label="Waktu Terbit"
                    type="datetime-local"
                    :value="old('published_at', optional($article->published_at)->format('Y-m-d\TH:i'))"
                    hint="Kosongkan untuk memakai waktu saat ini ketika diterbitkan." />

                <x-form.checkbox
                    name="is_featured"
                    label="Jadikan artikel unggulan"
                    :checked="$article->is_featured"
                    hint="Ditonjolkan besar di beranda. Jika lebih dari satu, yang terbaru dipakai." />
            </div>
        </x-admin.card>

        <x-admin.card title="Gambar Sampul">
            <x-form.image-field name="thumbnail" label="Thumbnail" :current="$article->thumbnail_path" />
        </x-admin.card>

        <div class="reveal flex flex-col gap-2">
            <button type="submit" class="admin-button admin-button-primary">{{ $submitLabel }}</button>
            <a href="{{ route('admin.articles.index') }}" class="admin-button admin-button-secondary">Batal</a>
        </div>
    </div>
</div>
