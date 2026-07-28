<?php

namespace Tests\Unit;

use App\Support\ProfanityFilter;
use Tests\TestCase;

/**
 * Dua sisi penyaring diuji dengan bobot yang sama.
 *
 * Bagian "harus lolos" bukan pelengkap: FPK memakai chat ini sebagai pintu
 * tanya warga soal kerukunan, jadi menolak pertanyaan yang wajar merugikan
 * lebih besar daripada meloloskan satu umpatan. Banyak kalimat di bawah adalah
 * jebakan yang sengaja memuat potongan kata terlarang di dalam kata yang sah —
 * "Rasulullah" memuat "asu", "Pantekosta" memuat "pantek", "pisang enak" memuat
 * "sange".
 */
class ProfanityFilterTest extends TestCase
{
    private ProfanityFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();

        ProfanityFilter::flush();
        $this->filter = new ProfanityFilter;
    }

    protected function tearDown(): void
    {
        ProfanityFilter::flush();

        parent::tearDown();
    }

    /**
     * @param  list<string>  $messages
     */
    private function assertAllPass(array $messages): void
    {
        foreach ($messages as $message) {
            $verdict = $this->filter->check($message);

            $this->assertFalse(
                $verdict->blocked,
                "Pesan wajar ikut tertolak: \"{$message}\" (kategori {$verdict->category}, pemicu "
                    .implode(', ', $verdict->matches).')'
            );
        }
    }

    /**
     * @param  list<string>  $messages
     */
    private function assertAllBlocked(array $messages, ?string $category = null): void
    {
        foreach ($messages as $message) {
            $verdict = $this->filter->check($message);

            $this->assertTrue($verdict->blocked, "Pesan kasar lolos penyaring: \"{$message}\"");

            if ($category !== null) {
                $this->assertSame($category, $verdict->category, "Kategori keliru untuk \"{$message}\".");
            }

            $this->assertNotEmpty($verdict->message, 'Penolakan wajib punya pesan untuk tamu.');
        }
    }

    public function test_ordinary_questions_are_never_blocked(): void
    {
        $this->assertAllPass([
            'Halo, saya ingin bertanya tentang agenda FPK bulan ini.',
            'Apakah ada acara buka puasa bersama tahun ini?',
            'Saya butuh informasi tentang pendaftaran anggota.',
            'Apa saja tugas pokok FPK Kota Malang?',
            'Mohon info kegiatan di sekitar Blimbing.',
            'Tolong kirim 500 ml air mineral ke sekretariat.',
            'Nomor saya 081234567890, mohon dihubungi.',
            'Rumah saya di RT 3 RW 4 ya pak.',
            'Alamat saya Jl. A. Yani no 5 Malang.',
        ]);
    }

    public function test_legitimate_words_containing_a_banned_fragment_are_not_blocked(): void
    {
        $this->assertAllPass([
            'Rasulullah mengajarkan toleransi antarumat.',
            'Saya mau daftar asuransi kesehatan.',
            'Bagaimana cara masuk menjadi pasukan pengibar bendera?',
            'Gereja Pantekosta di Malang alamatnya di mana?',
            'Apakah Pak Silitonga hadir pada rapat kemarin?',
            'Saya beli pisang enak di pasar besar.',
            'Suara peluit itu memekik di lapangan.',
            'Anak saya menabung di celengan.',
            'Analisis data kerukunan tahun lalu bagaimana?',
            'Kami jalan-jalan ke pantai dan santai di sana.',
            'Saya naik bis pak, agak telat sedikit.',
            'Konteks pertanyaan saya soal seleksi anggota.',
        ]);
    }

    /**
     * Kata berimbuhan tidak boleh ikut tertandai. Pencocokan memakai batas kata
     * justru supaya laporan warga soal kejahatan tetap bisa dikirim.
     */
    public function test_reports_about_crime_can_still_be_sent(): void
    {
        $this->assertAllPass([
            'Saya ingin melaporkan kasus penganiayaan anak.',
            'Berita pemerkosaan itu sangat meresahkan warga.',
            'Penculikan anak marak, bagaimana pencegahannya?',
            'Isu ini seperti bom waktu bagi kerukunan.',
            'Saya bakar ayam untuk acara syukuran.',
            'Saya tunggu kamu di kantor FPK ya.',
        ]);
    }

    /**
     * Inti keberadaan FPK: pertanyaan lintas agama dan suku harus bebas dikirim.
     */
    public function test_interfaith_questions_are_never_blocked(): void
    {
        $this->assertAllPass([
            'Bagaimana hukum makan babi bagi umat Islam?',
            'Apakah kamu makan babi?',
            'Toko itu menjual daging babi dan sapi.',
            'Bagaimana sikap FPK terhadap paham radikal?',
            'Apakah aliran itu sesat menurut MUI?',
            'Dia orang Jawa asli, istrinya orang Batak.',
            'Makanan khas Jawa apa saja yang terkenal?',
            'Kegiatan di masjid, gereja, dan vihara berjalan lancar.',
            'Acara di Klenteng Eng An Kiong kapan?',
            'Apa dasar hukum pembentukan FPK?',
            'Dasar negara kita adalah Pancasila.',
        ]);
    }

    public function test_it_blocks_common_indonesian_profanity(): void
    {
        $this->assertAllBlocked([
            'dasar anjing',
            'ANJING!!!',
            'bangsat kau',
            'goblok banget',
            'tolol banget kamu',
            'bacot',
        ], 'umpatan');
    }

    public function test_it_blocks_regional_language_profanity(): void
    {
        $this->assertAllBlocked([
            'jancok',          // Jawa Timur
            'asu tenan',       // Jawa
            'ndasmu',          // Jawa
            'belegug',         // Sunda
            'kehed',           // Sunda
            'cicing',          // Bali
            'kanina',          // Melayu
        ], 'umpatan');
    }

    public function test_it_blocks_sexual_content(): void
    {
        $this->assertAllBlocked([
            'kontol',
            'memek',
            'lonte',
            'pukimak',      // Minang/Melayu
            'kote',         // Manado
        ], 'seksual');
    }

    public function test_it_blocks_ethnic_and_religious_slurs(): void
    {
        $this->assertAllBlocked([
            'dasar cina',
            'jawa babi',
            'monyet papua',
            'usir orang papua',
            'bakar gereja itu sekarang',
            'cebong semua',
            'dasar kadrun',
            'dasar madura',
        ], 'sara');
    }

    public function test_it_blocks_violent_threats(): void
    {
        $this->assertAllBlocked([
            'bunuh kau',
            'aku akan bacok kamu',
            'kubunuh kau besok',
            'gue bacok lu',
            'mati kau',
            'mampus lu',
            'bakar rumahmu',
            'pasang bom di kantor',
            'awas kau',
        ], 'ancaman');
    }

    /**
     * Kata yang sah sendirian tetapi menjadi hinaan begitu ditujukan ke orang.
     */
    public function test_context_decides_whether_a_neutral_word_is_an_insult(): void
    {
        $this->assertAllPass([
            'Bagaimana pandangan Islam tentang babi?',
            'Kebun binatang itu punya monyet dan harimau.',
        ]);

        $this->assertAllBlocked([
            'dasar babi',
            'monyet lu',
            'si babi',
            'kamu kafir',
            'kamu murtad',
        ], 'kontekstual');
    }

    /**
     * Penyamaran yang lazim dipakai untuk menembus penyaring kata.
     */
    public function test_it_sees_through_disguised_spelling(): void
    {
        $this->assertAllBlocked([
            'AnJiNg',                  // huruf besar-kecil diacak
            'anjinggggg',              // huruf diulang
            '4nj1ng lu',               // angka menggantikan huruf
            'g0bl0k',                  // angka menggantikan huruf
            '@nj1ng',                  // simbol menggantikan huruf
            'b4j1ng4n',
            'a n j i n g',             // dipisah spasi
            'a  n  j  i  n  g',        // dipisah spasi ganda
            'an ji ng',                // dipisah penggalan
            'a-n-j-i-n-g',             // dipisah tanda hubung
            'k.o.n.t.o.l',             // dipisah titik
            'b*a*n*g*s*a*t',           // dipisah bintang
            'n g e n t o t',
            "anj\u{200B}ing",          // sisipan karakter tak terlihat
            'аnjing',                  // huruf "a" Kiril
            'ａｎｊｉｎｇ',                // huruf lebar penuh
            '𝐚𝐧𝐣𝐢𝐧𝐠',                // huruf tebal matematis
            'ⓐⓝⓙⓘⓝⓖ',                // huruf berlingkaran
        ]);
    }

    public function test_it_ignores_empty_input(): void
    {
        $this->assertAllPass(['', '   ', "\n\t"]);

        $this->assertFalse($this->filter->check(null)->blocked);
    }

    public function test_it_can_be_switched_off(): void
    {
        config()->set('profanity.enabled', false);

        $this->assertAllPass(['dasar anjing', 'bunuh kau']);
    }

    public function test_is_clean_mirrors_the_verdict(): void
    {
        $this->assertTrue($this->filter->isClean('Halo, apa kabar?'));
        $this->assertFalse($this->filter->isClean('dasar anjing'));
    }

    public function test_the_verdict_names_the_category_and_the_trigger(): void
    {
        $verdict = $this->filter->check('dasar anjing');

        $this->assertTrue($verdict->blocked);
        $this->assertSame('umpatan', $verdict->category);
        $this->assertNotEmpty($verdict->matches);
        $this->assertStringContainsString('kata kasar', (string) $verdict->message);
    }
}
