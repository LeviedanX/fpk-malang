<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Penyaring kata pada jalur nyata: dari permintaan HTTP tamu sampai ke tabel.
 */
class ChatModerationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_abusive_first_message_is_rejected_and_no_conversation_is_created(): void
    {
        $before = ChatConversation::query()->count();

        $this->postJson(route('chat.store'), ['body' => 'dasar anjing'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');

        // Penting: percakapan tidak boleh ikut lahir dari pesan yang ditolak,
        // supaya kotak masuk admin tidak terisi baris kosong.
        $this->assertSame($before, ChatConversation::query()->count());
        $this->assertDatabaseMissing('chat_messages', ['body' => 'dasar anjing']);
    }

    public function test_the_guest_is_told_why_the_message_was_rejected(): void
    {
        $this->postJson(route('chat.store'), ['body' => 'bunuh kau'])
            ->assertStatus(422)
            ->assertJsonPath('errors.body.0', fn (string $message): bool => str_contains($message, 'ancaman'));
    }

    public function test_each_category_carries_its_own_explanation(): void
    {
        $cases = [
            'dasar anjing' => 'kata kasar',
            'kontol' => 'seksual',
            'dasar cina' => 'suku, agama, ras',
            'bunuh kau' => 'ancaman',
        ];

        foreach ($cases as $body => $expected) {
            $this->postJson(route('chat.store'), ['body' => $body])
                ->assertStatus(422)
                ->assertJsonPath(
                    'errors.body.0',
                    fn (string $message): bool => str_contains($message, $expected),
                );
        }
    }

    public function test_a_disguised_message_is_rejected_too(): void
    {
        foreach (['4nj1ng', 'a n j i n g', 'k.o.n.t.o.l'] as $body) {
            $this->postJson(route('chat.store'), ['body' => $body])
                ->assertStatus(422)
                ->assertJsonValidationErrors('body');
        }
    }

    public function test_a_polite_message_still_goes_through(): void
    {
        $this->postJson(route('chat.store'), [
            'body' => 'Halo, saya ingin bertanya soal agenda FPK bulan ini.',
        ])->assertCreated();
    }

    /**
     * Pertanyaan lintas iman adalah alasan utama kanal ini ada. Kalau penyaring
     * memblokirnya, penyaringnya yang salah.
     */
    public function test_interfaith_questions_are_delivered(): void
    {
        $this->postJson(route('chat.store'), [
            'body' => 'Bagaimana hukum makan babi bagi umat Islam, dan bagaimana FPK menjaga toleransi?',
        ])->assertCreated();
    }

    public function test_an_image_without_text_is_unaffected(): void
    {
        Storage::fake('public');

        $this->postJson(route('chat.store'), [
            'image' => UploadedFile::fake()->image('undangan.png', 800, 600),
        ])->assertCreated();
    }

    /**
     * Admin harus tetap bisa mengutip kalimat kasar tamu saat menanggapi atau
     * mendokumentasikan laporan, jadi balasannya tidak pernah disaring.
     */
    public function test_admin_replies_are_not_filtered(): void
    {
        $this->postJson(route('chat.store'), ['body' => 'Halo admin'])->assertCreated();
        $conversation = ChatConversation::query()->latest('id')->firstOrFail();

        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.chat.reply', $conversation), [
                'body' => 'Kami mencatat laporan Anda soal kata "anjing" yang dipakai pelaku.',
            ])
            ->assertCreated();
    }

    public function test_the_filter_can_be_switched_off_by_configuration(): void
    {
        config()->set('profanity.enabled', false);

        $this->postJson(route('chat.store'), ['body' => 'dasar anjing'])->assertCreated();
    }
}
