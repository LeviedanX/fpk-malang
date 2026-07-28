<?php

namespace App\Support;

use Normalizer;

/**
 * Menyeragamkan teks bebas dari tamu sebelum dicocokkan dengan daftar kata
 * terlarang.
 *
 * Penyaring kata mudah dikelabui bila teks dibaca apa adanya: "ANJING",
 * "anjiiing", "4nj1ng", "аnjing" (huruf "a" Kiril), dan "a n j i n g" adalah
 * kata yang sama bagi pembaca manusia, tetapi lima string berbeda bagi regex.
 * Kelas ini merapikan seluruh variasi itu menjadi satu bentuk baku sehingga
 * daftar kata cukup memuat ejaan normalnya saja.
 *
 * Hasil normalisasi selalu berupa huruf a-z dan spasi tunggal — tanpa angka,
 * tanda baca, maupun aksen — sehingga aman dipakai untuk pencocokan kata utuh.
 */
class TextNormalizer
{
    /**
     * Huruf non-latin yang bentuknya nyaris identik dengan huruf latin.
     * Sering dipakai untuk menembus penyaring karena terlihat sama di layar.
     *
     * @var array<string, string>
     */
    private const HOMOGLYPHS = [
        // Kiril
        'а' => 'a', 'в' => 'b', 'с' => 'c', 'е' => 'e', 'н' => 'h', 'к' => 'k',
        'м' => 'm', 'о' => 'o', 'р' => 'p', 'ѕ' => 's', 'т' => 't', 'у' => 'y',
        'х' => 'x', 'і' => 'i', 'ј' => 'j', 'ԁ' => 'd', 'ѵ' => 'v',
        // Yunani
        'α' => 'a', 'ο' => 'o', 'ρ' => 'p', 'ι' => 'i', 'κ' => 'k', 'ν' => 'v',
        'τ' => 't', 'υ' => 'u', 'χ' => 'x', 'ε' => 'e',
        // Latin varian
        'ɡ' => 'g', 'ı' => 'i', 'ǀ' => 'i',
    ];

    /**
     * Angka yang dipakai menggantikan huruf.
     *
     * Hanya diterapkan pada penggalan yang memuat sedikitnya satu huruf, supaya
     * angka murni — nomor telepon, tahun, nominal — tidak ikut berubah menjadi
     * huruf dan membentuk kata yang tidak pernah ditulis siapa pun.
     *
     * @var array<string, string>
     */
    private const LEET_DIGITS = [
        '4' => 'a', '8' => 'b', '3' => 'e', '6' => 'g', '9' => 'g',
        '1' => 'i', '0' => 'o', '5' => 's', '7' => 't',
    ];

    /**
     * Simbol yang dipakai menggantikan huruf.
     *
     * Berbeda dari angka, simbol ini harus dipulihkan SEBELUM teks dipecah
     * menjadi penggalan — kalau tidak, ia keburu dibuang sebagai pemisah dan
     * "@nj1ng" hanya menyisakan "njing" yang tidak cocok dengan daftar mana pun.
     *
     * @var array<string, string>
     */
    private const LEET_SYMBOLS = [
        '@' => 'a', '$' => 's', '!' => 'i', '|' => 'i', '+' => 't',
    ];

    /** Karakter tak terlihat yang bisa disisipkan di tengah kata. */
    private const INVISIBLE = "/[\x{00AD}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}\x{FEFF}]/u";

    /**
     * Bentuk baku sebuah teks: huruf kecil, tanpa aksen, homoglif dipulihkan,
     * angka gaya alay dikembalikan menjadi huruf, huruf berulang diringkas, dan
     * antarkata hanya satu spasi.
     */
    public static function normalize(string $text): string
    {
        $text = preg_replace(self::INVISIBLE, '', $text) ?? $text;

        // NFKD memecah aksen menjadi huruf dasar + tanda, sekaligus mengubah
        // karakter lebar penuh (ａ) dan bentuk khusus lain ke padanan biasa.
        if (class_exists(Normalizer::class)) {
            $text = Normalizer::normalize($text, Normalizer::FORM_KD) ?: $text;
        }

        // Buang tanda diakritik sisa pemecahan di atas.
        $text = preg_replace('/\p{Mn}+/u', '', $text) ?? $text;

        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, self::HOMOGLYPHS);
        $text = self::restoreSymbolLeet($text);

        // Apa pun selain huruf dan angka menjadi pemisah. Ini sekaligus
        // membuang tanda baca yang diselipkan di tengah kata.
        $raw = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];

        foreach ($raw as $token) {
            // Penggalan tanpa huruf sama sekali (angka murni) dibuang: ia tidak
            // pernah menjadi kata, dan menerjemahkannya lewat tabel leet justru
            // memunculkan kecocokan palsu.
            if (preg_match('/\p{L}/u', $token) !== 1) {
                continue;
            }

            $token = strtr($token, self::LEET_DIGITS);

            // Sisa angka yang tidak ada di tabel leet tidak membawa makna.
            $token = preg_replace('/\p{N}+/u', '', $token) ?? $token;

            // "anjiiing" dan "anjiing" diringkas menjadi "anjing". Daftar kata
            // diringkas dengan aturan yang sama sehingga keduanya bertemu.
            $token = self::collapseRepeats($token);

            if ($token !== '') {
                $tokens[] = $token;
            }
        }

        return implode(' ', $tokens);
    }

    /**
     * Menyambung rentetan penggalan pendek menjadi satu kata.
     *
     * Menangani penyamaran "a n j i n g", "a.n.j.i.n.g", dan "an ji ng" — yang
     * setelah normalisasi sama-sama menjadi deretan penggalan satu atau dua
     * huruf. Ambang tiga penggalan berturut-turut dipilih supaya kalimat wajar
     * tidak ikut tersambung: bahasa Indonesia memang punya kata pendek ("di",
     * "ke", "ya"), tetapi jarang tiga sekaligus berurutan.
     */
    public static function glue(string $normalized): string
    {
        if ($normalized === '') {
            return '';
        }

        $tokens = explode(' ', $normalized);
        $output = [];
        $run = [];

        $flush = function () use (&$run, &$output): void {
            if ($run === []) {
                return;
            }

            // Rentetan cukup panjang berarti penyamaran: sambung menjadi satu
            // kata. Bila tidak, kembalikan apa adanya.
            $output[] = count($run) >= 3 ? implode('', $run) : implode(' ', $run);
            $run = [];
        };

        foreach ($tokens as $token) {
            if (mb_strlen($token) <= 2) {
                $run[] = $token;

                continue;
            }

            $flush();
            $output[] = $token;
        }

        $flush();

        return implode(' ', $output);
    }

    /**
     * Teks tanpa spasi sama sekali, dipakai jaring terakhir penyaring.
     */
    public static function squeeze(string $normalized): string
    {
        return str_replace(' ', '', $normalized);
    }

    /**
     * Apakah pesan memperlihatkan tanda penyamaran yang disengaja?
     *
     * Dipakai sebagai gerbang sebelum pencocokan potongan kata dijalankan.
     * Pencocokan itu ampuh tetapi kehilangan batas kata, sehingga pada kalimat
     * normal ia bisa membaca "pisang enak" sebagai kata terlarang. Dengan
     * gerbang ini, kalimat biasa tidak pernah sampai ke sana.
     */
    public static function looksObfuscated(string $original, string $normalized): bool
    {
        if ($normalized !== '') {
            $singles = 0;

            foreach (explode(' ', $normalized) as $token) {
                if (mb_strlen($token) === 1) {
                    $singles++;
                }
            }

            if ($singles >= 3) {
                return true;
            }
        }

        // Titik atau tanda hubung yang diselipkan di antara huruf: "b.a.b.i".
        return preg_match_all('/\p{L}[.\-_*+~·•]\p{L}/u', $original) >= 2;
    }

    /**
     * Mengembalikan simbol pengganti huruf, tetapi hanya bila ia menempel pada
     * huruf berikutnya — pertanda simbol itu dipakai DI DALAM kata.
     *
     * Syarat "menempel" inilah yang menjaga tanda baca biasa tetap utuh:
     * "Terima kasih!" dan "Halo, ada acara?" tidak berubah karena tanda bacanya
     * diikuti spasi, sedangkan "@njing" dan "b$at" dipulihkan.
     */
    private static function restoreSymbolLeet(string $text): string
    {
        return preg_replace_callback(
            '/[@$!|+](?=\p{L})/u',
            static fn (array $match): string => self::LEET_SYMBOLS[$match[0]] ?? $match[0],
            $text
        ) ?? $text;
    }

    /**
     * Meringkas huruf yang diulang berturut-turut menjadi satu.
     */
    private static function collapseRepeats(string $token): string
    {
        return preg_replace('/(.)\1+/u', '$1', $token) ?? $token;
    }
}
