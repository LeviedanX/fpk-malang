<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChatConversationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatMessageRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Support\ChatManager;
use App\Support\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    private const THREAD_PAGE_SIZE = 50;

    public function __construct(private readonly ChatManager $chat) {}

    public function index(Request $request): View
    {
        $filter = in_array($request->query('filter'), ['unread', 'open', 'closed'], true)
            ? $request->query('filter')
            : 'all';

        $conversations = ChatConversation::query()
            ->when($filter === 'unread', fn ($query) => $query->where('admin_unread_count', '>', 0))
            ->when($filter === 'open', fn ($query) => $query->open())
            ->when($filter === 'closed', fn ($query) => $query->where('status', ChatConversationStatus::Closed))
            ->recentFirst()
            ->paginate(config('fpk.pagination.chat_admin', 20))
            ->withQueryString();

        // One extra query for the whole page instead of one per row: fetch the
        // latest message of every conversation on screen and attach it.
        $this->attachLatestMessages($conversations->getCollection());

        return view('admin.chat.index', [
            'conversations' => $conversations,
            'filter' => $filter,
            'counts' => $this->inboxCounts(),
        ]);
    }

    public function show(Request $request, ChatConversation $conversation): View
    {
        $this->chat->markReadForAdmin($conversation);

        $messages = $conversation->messages()
            ->orderByDesc('id')
            ->limit(self::THREAD_PAGE_SIZE)
            ->get()
            ->reverse()
            ->values();

        return view('admin.chat.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'hasMore' => $conversation->messages_count > $messages->count(),
            // Conversations sharing this fingerprint came from the same device
            // and IP — the tracking view the admin uses to tell guests apart.
            'relatedCount' => ChatConversation::query()
                ->where('visitor_hash', $conversation->visitor_hash)
                ->whereKeyNot($conversation->id)
                ->count(),
        ]);
    }

    /**
     * Live tail of one thread. Mirrors the guest poll: 204 when idle so an open
     * admin tab costs almost nothing while waiting for a reply.
     */
    public function poll(Request $request, ChatConversation $conversation): JsonResponse|Response
    {
        $after = max(0, (int) $request->query('after', 0));

        $messages = $conversation->messages()
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(self::THREAD_PAGE_SIZE)
            ->get();

        if ($messages->isEmpty()) {
            return response()->noContent();
        }

        // The thread is on screen, so incoming guest messages are read on
        // arrival and the sidebar badge never lights up for this conversation.
        $this->chat->markReadForAdmin($conversation);

        return response()->json([
            'messages' => $messages->map->toWireArray()->all(),
        ]);
    }

    public function history(Request $request, ChatConversation $conversation): JsonResponse
    {
        $before = max(0, (int) $request->query('before', 0));

        if ($before === 0) {
            return response()->json(['messages' => [], 'has_more' => false]);
        }

        $messages = $conversation->messages()
            ->where('id', '<', $before)
            ->orderByDesc('id')
            ->limit(self::THREAD_PAGE_SIZE)
            ->get();

        $oldest = $messages->last();

        return response()->json([
            'messages' => $messages->reverse()->values()->map->toWireArray()->all(),
            'has_more' => $oldest !== null && $conversation->messages()
                ->where('id', '<', $oldest->id)
                ->exists(),
        ]);
    }

    public function reply(ChatMessageRequest $request, ChatConversation $conversation): JsonResponse|RedirectResponse
    {
        $message = $this->chat->postAdminMessage(
            $conversation,
            $request->user(),
            $request->messageBody(),
            $request->file('image'),
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => $message->toWireArray()], 201);
        }

        return back();
    }

    /**
     * Unread summary for the sidebar badge.
     *
     * Every admin tab hits this on a timer, so it stays limited to two counts
     * over the small conversations table, both served by the
     * admin_unread_count index. Nothing here touches chat_messages.
     */
    public function unread(): JsonResponse
    {
        return response()->json([
            'total' => ChatManager::adminUnreadTotal(),
            'conversations' => ChatConversation::query()
                ->where('admin_unread_count', '>', 0)
                ->count(),
        ]);
    }

    public function status(Request $request, ChatConversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,closed'],
        ]);

        $conversation->update(['status' => $validated['status']]);

        return back()->with('status', $validated['status'] === 'closed'
            ? 'Percakapan ditandai selesai.'
            : 'Percakapan diaktifkan kembali.');
    }

    public function block(Request $request, ChatConversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'blocked' => ['required', 'boolean'],
        ]);

        $conversation->update(['is_blocked' => $validated['blocked']]);

        return back()->with('status', $validated['blocked']
            ? 'Percakapan diblokir. Tamu tidak dapat mengirim pesan lagi.'
            : 'Blokir percakapan dicabut.');
    }

    public function destroy(ChatConversation $conversation): RedirectResponse
    {
        // Collect attachments before the cascade removes the rows, so no
        // orphaned image is left behind on the public disk.
        $attachments = $conversation->messages()
            ->whereNotNull('attachment_path')
            ->pluck('attachment_path');

        DB::transaction(fn () => $conversation->delete());

        $attachments->each(fn (string $path) => ImageStorage::delete($path));

        return redirect()
            ->route('admin.chat.index')
            ->with('status', 'Percakapan beserta lampirannya berhasil dihapus.');
    }

    /**
     * @param  Collection<int, ChatConversation>  $conversations
     */
    private function attachLatestMessages($conversations): void
    {
        if ($conversations->isEmpty()) {
            return;
        }

        $latestIds = ChatMessage::query()
            ->whereIn('conversation_id', $conversations->modelKeys())
            ->groupBy('conversation_id')
            ->select(DB::raw('MAX(id) as id'))
            ->pluck('id');

        $latest = ChatMessage::query()
            ->whereKey($latestIds)
            ->get()
            ->keyBy('conversation_id');

        $conversations->each(function (ChatConversation $conversation) use ($latest): void {
            $message = $latest->get($conversation->id);

            $conversation->setRelation(
                'messages',
                $message ? collect([$message]) : collect(),
            );
        });
    }

    /**
     * @return array{all: int, unread: int, open: int, closed: int}
     */
    private function inboxCounts(): array
    {
        $row = ChatConversation::query()
            ->selectRaw('COUNT(*) as all_count')
            ->selectRaw('SUM(admin_unread_count > 0) as unread_count')
            ->selectRaw('SUM(status = ?) as open_count', [ChatConversationStatus::Open->value])
            ->selectRaw('SUM(status = ?) as closed_count', [ChatConversationStatus::Closed->value])
            ->first();

        return [
            'all' => (int) ($row->all_count ?? 0),
            'unread' => (int) ($row->unread_count ?? 0),
            'open' => (int) ($row->open_count ?? 0),
            'closed' => (int) ($row->closed_count ?? 0),
        ];
    }
}
