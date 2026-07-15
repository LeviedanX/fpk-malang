@if (session('status'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 6000)"
        class="flex items-start justify-between gap-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        role="status"
    >
        <span>{{ session('status') }}</span>
        <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800" aria-label="Tutup notifikasi">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
        <p class="font-medium">Terdapat {{ $errors->count() }} kesalahan pada isian:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
