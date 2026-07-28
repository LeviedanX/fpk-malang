{{-- Tombol kembali ke atas untuk halaman publik.
     Tersembunyi saat halaman masih di puncak; ditampilkan oleh app.js setelah
     pengguna menggulir. Posisi fixed sehingga tidak menggeser layout. --}}
<button
    type="button"
    class="back-to-top"
    data-back-to-top
    aria-label="Kembali ke atas"
    title="Kembali ke atas"
    hidden
>
    <svg class="back-to-top__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="M12 19V5" />
        <path d="m5 12 7-7 7 7" />
    </svg>
</button>
