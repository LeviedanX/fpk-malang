<?php

namespace App\Support;

/**
 * Penyaring kata terlarang untuk pesan tamu.
 *
 * Daftar kata, pola, dan pengecualiannya ada di config/profanity.php; kelas ini
 * hanya mesin pencocoknya. Empat lapis pemeriksaan dijalankan berurutan dan
 * berhenti pada temuan pertama:
 *
 *  1. POLA per kategori — regex untuk ancaman dan ujaran SARA, yang bentuknya
 *     memang frasa ("bunuh kau", "bakar gereja"), bukan kata tunggal.
 *  2. ISTILAH per kategori — pencocokan KATA UTUH pada teks hasil normalisasi
 *     dan pada teks hasil penyambungan penggalan pendek.
 *  3. KONTEKSTUAL — kata yang sah pada percakapan biasa dan baru dihitung
 *     sebagai hinaan bila bersebelahan dengan penanda sasaran.
 *  4. POTONGAN KATA pada teks rapat — jaring terakhir, hanya menyala bila pesan
 *     memperlihatkan tanda penyamaran yang disengaja.
 *
 * Seluruh pencocokan memakai batas kata, bukan potongan kata, kecuali lapis 4.
 * Ini keputusan yang disengaja dan bukan kelalaian: tanpa batas kata "asu" ikut
 * menandai "rasul", "asuransi", dan "masuk"; "sange" ikut menandai "pisang
 * enak"; "pantek" ikut menandai "Pantekosta". Pada layanan pesan sebuah forum
 * kerukunan, menolak pertanyaan yang wajar jauh lebih merugikan daripada
 * meloloskan satu umpatan yang ditulis dengan penyamaran berlapis.
 */
class ProfanityFilter
{
    /**
     * Regex istilah per kategori, dikompilasi sekali per proses.
     *
     * @var array<string, string>
     */
    private static array $termPatterns = [];

    /**
     * Istilah kategori dalam bentuk baku, untuk lapis 4.
     *
     * @var array<string, list<string>>
     */
    private static array $normalizedTerms = [];

    public function check(?string $text): ProfanityVerdict
    {
        $original = (string) $text;

        if (! config('profanity.enabled', true) || trim($original) === '') {
            return ProfanityVerdict::clean();
        }

        $normalized = TextNormalizer::normalize($original);

        if ($normalized === '') {
            return ProfanityVerdict::clean();
        }

        // Teks yang penggalan pendeknya sudah disambung, supaya "a n j i n g"
        // ikut terbaca. Bila tidak ada yang berubah, cukup periksa satu bentuk.
        $glued = TextNormalizer::glue($normalized);
        $haystacks = $glued === $normalized ? [$normalized] : [$normalized, $glued];

        /** @var array<string, array<string, mixed>> $categories */
        $categories = config('profanity.categories', []);

        foreach ($categories as $name => $category) {
            $verdict = $this->matchCategory((string) $name, $category, $haystacks);

            if ($verdict->blocked) {
                return $verdict;
            }
        }

        $verdict = $this->matchContextual($haystacks);

        if ($verdict->blocked) {
            return $verdict;
        }

        if (TextNormalizer::looksObfuscated($original, $normalized)) {
            return $this->matchDisguised($normalized, $categories);
        }

        return ProfanityVerdict::clean();
    }

    /**
     * Apakah pesan lolos pemeriksaan? Pembungkus ringkas untuk pemakaian
     * di luar validasi.
     */
    public function isClean(?string $text): bool
    {
        return ! $this->check($text)->blocked;
    }

    /**
     * @param  array<string, mixed>  $category
     * @param  list<string>  $haystacks
     */
    private function matchCategory(string $name, array $category, array $haystacks): ProfanityVerdict
    {
        $message = (string) ($category['message'] ?? 'Pesan mengandung kata yang tidak diperbolehkan.');

        foreach ((array) ($category['patterns'] ?? []) as $pattern) {
            foreach ($haystacks as $haystack) {
                if (preg_match($pattern, $haystack, $found) === 1) {
                    return ProfanityVerdict::blocked($name, $message, [$found[0]]);
                }
            }
        }

        $regex = $this->termPattern($name, (array) ($category['terms'] ?? []));

        if ($regex === null) {
            return ProfanityVerdict::clean();
        }

        foreach ($haystacks as $haystack) {
            if (preg_match($regex, $haystack, $found) === 1) {
                return ProfanityVerdict::blocked($name, $message, [$found[0]]);
            }
        }

        return ProfanityVerdict::clean();
    }

    /**
     * Lapis 3: kata yang baru menjadi hinaan bila bersebelahan dengan penanda
     * sasaran. "Babi" pada pertanyaan makanan halal dibiarkan lewat; "dasar
     * babi" dan "babi kau" tidak.
     *
     * @param  list<string>  $haystacks
     */
    private function matchContextual(array $haystacks): ProfanityVerdict
    {
        /** @var array<string, mixed> $config */
        $config = config('profanity.contextual', []);

        $terms = array_flip($this->normalizeList((array) ($config['terms'] ?? [])));
        $markers = array_flip($this->normalizeList((array) ($config['markers'] ?? [])));

        if ($terms === [] || $markers === []) {
            return ProfanityVerdict::clean();
        }

        $distance = max(1, (int) ($config['distance'] ?? 1));
        $message = (string) ($config['message'] ?? 'Pesan terbaca sebagai hinaan.');

        foreach ($haystacks as $haystack) {
            $tokens = explode(' ', $haystack);
            $total = count($tokens);

            foreach ($tokens as $index => $token) {
                if (! isset($terms[$token])) {
                    continue;
                }

                $from = max(0, $index - $distance);
                $to = min($total - 1, $index + $distance);

                for ($near = $from; $near <= $to; $near++) {
                    if ($near !== $index && isset($markers[$tokens[$near]])) {
                        return ProfanityVerdict::blocked(
                            'kontekstual',
                            $message,
                            [$tokens[$near].' '.$token],
                        );
                    }
                }
            }
        }

        return ProfanityVerdict::clean();
    }

    /**
     * Lapis 4: pencocokan potongan kata pada teks yang seluruh spasinya dibuang.
     *
     * Hanya dipanggil untuk pesan yang sudah terbukti disamarkan. Kata sah yang
     * kebetulan memuat istilah terlarang disembunyikan lebih dulu, dan istilah
     * yang terlalu pendek dilewati — keduanya menahan kecocokan palsu yang
     * pasti muncul begitu batas kata hilang.
     *
     * @param  array<string, array<string, mixed>>  $categories
     */
    private function matchDisguised(string $normalized, array $categories): ProfanityVerdict
    {
        $minimum = max(1, (int) config('profanity.compact_min_length', 5));
        $masked = $normalized;

        foreach ($this->normalizeList((array) config('profanity.exceptions', [])) as $safe) {
            $masked = preg_replace(
                '/(?<![\p{L}\p{N}])'.preg_quote($safe, '/').'(?![\p{L}\p{N}])/u',
                ' ',
                $masked
            ) ?? $masked;
        }

        $squeezed = TextNormalizer::squeeze($masked);

        if ($squeezed === '') {
            return ProfanityVerdict::clean();
        }

        foreach ($categories as $name => $category) {
            foreach ($this->categoryTerms((string) $name, (array) ($category['terms'] ?? [])) as $term) {
                if (mb_strlen($term) >= $minimum && str_contains($squeezed, $term)) {
                    return ProfanityVerdict::blocked(
                        (string) $name,
                        (string) ($category['message'] ?? 'Pesan mengandung kata yang tidak diperbolehkan.'),
                        [$term],
                    );
                }
            }
        }

        return ProfanityVerdict::clean();
    }

    /**
     * Regex kata utuh untuk seluruh istilah satu kategori, dikompilasi sekali.
     *
     * @param  list<string>  $terms
     */
    private function termPattern(string $name, array $terms): ?string
    {
        if (! array_key_exists($name, self::$termPatterns)) {
            $normalized = $this->categoryTerms($name, $terms);

            self::$termPatterns[$name] = $normalized === []
                ? ''
                : '/(?<![\p{L}\p{N}])(?:'
                    .implode('|', array_map(static fn (string $term): string => preg_quote($term, '/'), $normalized))
                    .')(?![\p{L}\p{N}])/u';
        }

        return self::$termPatterns[$name] === '' ? null : self::$termPatterns[$name];
    }

    /**
     * Istilah kategori dalam bentuk baku. Daftar kata melewati normalisasi yang
     * sama persis dengan teks pesan — tanpa itu "anjiing" pada pesan tidak akan
     * pernah bertemu "anjing" pada daftar.
     *
     * @param  list<string>  $terms
     * @return list<string>
     */
    private function categoryTerms(string $name, array $terms): array
    {
        return self::$normalizedTerms[$name] ??= $this->normalizeList($terms);
    }

    /**
     * @param  array<int, string>  $values
     * @return list<string>
     */
    private function normalizeList(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            $clean = TextNormalizer::normalize((string) $value);

            if ($clean !== '') {
                $normalized[$clean] = true;
            }
        }

        return array_keys($normalized);
    }

    /**
     * Membuang cache regex. Dipakai pengujian yang mengganti daftar kata.
     */
    public static function flush(): void
    {
        self::$termPatterns = [];
        self::$normalizedTerms = [];
    }
}
