<?php

namespace App\Rules;

use App\Support\ProfanityFilter;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

/**
 * Menolak pesan tamu yang memuat umpatan, hinaan SARA, ancaman, atau konten
 * seksual. Pesan yang ditolak tidak pernah tersimpan, sehingga isinya tidak
 * sempat muncul di kotak masuk admin.
 */
class CleanMessage implements ValidationRule
{
    public function __construct(private readonly ProfanityFilter $filter) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $verdict = $this->filter->check($value);

        if (! $verdict->blocked) {
            return;
        }

        // Kategori dan istilah pemicunya dicatat agar daftar kata bisa
        // ditajamkan dari kejadian nyata. Isi pesan dan identitas pengirim
        // sengaja tidak ikut ditulis ke log.
        Log::warning('Pesan chat tamu ditolak penyaring kata.', [
            'kategori' => $verdict->category,
            'pemicu' => $verdict->matches,
        ]);

        $fail((string) $verdict->message);
    }
}
