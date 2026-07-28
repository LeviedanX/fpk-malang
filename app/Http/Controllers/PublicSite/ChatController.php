<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveChatGuest;
use App\Http\Requests\Chat\ChatMessageRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Support\ChatManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatController extends Controller
{
    /**
     * Newest messages returned per request. The widget backfills older ones on
     * demand, so a long thread never ships in a single response.
     */
    private const PAGE_SIZE = 30;

    public function __construct(private readonly ChatManager $chat) {}

    /**
     * Opening handshake. A visitor who has never written has no conversation
     * yet — the widget renders its greeting from an empty payload and the row
     * is only created when they actually send something, so bots that merely
     * load the page cost nothing.
     */
    public function show(Request $request): JsonResponse
    {
        $conversation = $this->conversation($request);

        if ($conversation === null) {
            return response()->json([
                'conversation' => null,
                'messages' => [],
                'unread' => 0,
            ]);
        }

        $messages = $conversation->messages()
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE)
            ->get()
            ->reverse()
            ->values();

        $unread = $conversation->guest_unread_count;

        if ($request->boolean('seen')) {
            $this->chat->markReadForGuest($conversation);
        }

        return response()->json([
            'conversation' => $this->conversationPayload($conversation),
            'messages' => $messages->map->toWireArray()->all(),
            'has_more' => $conversation->messages_count > $messages->count(),
            'unread' => $unread,
        ]);
    }

    /**
     * Polling endpoint. Deliberately answers 204 with an empty body when there
     * is nothing new: that is the overwhelmingly common case, and it keeps an
     * idle widget's traffic down to response headers only. The lookup rides
     * the (conversation_id, id) index, so it never scans the thread.
     */
    public function poll(Request $request): JsonResponse|Response
    {
        $conversation = $this->conversation($request);

        if ($conversation === null) {
            return response()->noContent();
        }

        $after = max(0, (int) $request->query('after', 0));

        $messages = $conversation->messages()
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(self::PAGE_SIZE)
            ->get();

        if ($messages->isEmpty()) {
            return response()->noContent();
        }

        // The guest is looking at the panel, so anything just delivered counts
        // as read. Guarded by the badge counter, so this is a no-op most polls.
        if ($request->boolean('seen')) {
            $this->chat->markReadForGuest($conversation);
        }

        return response()->json([
            'messages' => $messages->map->toWireArray()->all(),
            'unread' => $conversation->guest_unread_count,
        ]);
    }

    /**
     * Older messages, walking backwards from the oldest one already on screen.
     */
    public function history(Request $request): JsonResponse
    {
        $conversation = $this->conversation($request);
        $before = max(0, (int) $request->query('before', 0));

        if ($conversation === null || $before === 0) {
            return response()->json(['messages' => [], 'has_more' => false]);
        }

        $messages = $conversation->messages()
            ->where('id', '<', $before)
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE)
            ->get();

        $oldest = $messages->last();

        return response()->json([
            'messages' => $messages->reverse()->values()->map->toWireArray()->all(),
            'has_more' => $oldest !== null && $conversation->messages()
                ->where('id', '<', $oldest->id)
                ->exists(),
        ]);
    }

    public function store(ChatMessageRequest $request): JsonResponse
    {
        $conversation = $this->conversation($request);
        $isNew = $conversation === null;

        if ($isNew) {
            $conversation = $this->chat->startConversation($request);
        } else {
            $this->chat->touchIdentity($conversation, $request);
        }

        $message = $this->chat->postGuestMessage(
            $conversation,
            $request->messageBody(),
            $request->file('image'),
        );

        return response()->json([
            // Only handed out on creation: the client stores it once and the
            // token never travels again except as the request header.
            'token' => $isNew ? $conversation->public_token : null,
            'conversation' => $this->conversationPayload($conversation->refresh()),
            'message' => $message->toWireArray(),
        ], 201);
    }

    private function conversation(Request $request): ?ChatConversation
    {
        $conversation = $request->attributes->get(ResolveChatGuest::ATTRIBUTE);

        return $conversation instanceof ChatConversation ? $conversation : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationPayload(ChatConversation $conversation): array
    {
        return [
            'label' => $conversation->guest_label,
            'status' => $conversation->status->value,
            'last_message_id' => (int) ChatMessage::query()
                ->where('conversation_id', $conversation->id)
                ->max('id'),
        ];
    }
}
