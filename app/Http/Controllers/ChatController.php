<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // 1️⃣ Start or get existing chat
    public function startChat(Request $request)
    {
        $request->validate(['receiver_id' => 'required|exists:users,id']);

        $userId = Auth::id();
        $receiverId = $request->receiver_id;

        if ($userId == $receiverId) {
            return response()->json(['error' => 'You cannot chat with yourself'], 400);
        }

        // Check if chat already exists (in any order)
        $chat = Chat::where(function ($q) use ($userId, $receiverId) {
            $q->where('user_one', $userId)->where('user_two', $receiverId);
        })->orWhere(function ($q) use ($userId, $receiverId) {
            $q->where('user_one', $receiverId)->where('user_two', $userId);
        })->first();

        if (!$chat) {
            $chat = Chat::create([
                'user_one' => $userId,
                'user_two' => $receiverId,
            ]);
        }

        return response()->json([
            'message' => 'Chat started successfully',
            'chat' => $chat
        ]);
    }

    // 2️⃣ Get all chats of the logged-in user
    public function getChats()
    {
        $userId = Auth::id();
        $chats = Chat::where('user_one', $userId)
            ->orWhere('user_two', $userId)
            ->with(['userOne:id,name,profile_image', 'userTwo:id,name,profile_image'])
            ->latest()
            ->get();

        return response()->json($chats);
    }

    // 3️⃣ Send a message in a chat
    public function sendMessage(Request $request, $chatId)
    {
        $request->validate(['message' => 'required|string']);

        $chat = Chat::find($chatId);
        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $userId = Auth::id();

        if ($chat->user_one != $userId && $chat->user_two != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $msg = Message::create([
            'chat_id' => $chatId,
            'sender_id' => $userId,
            'message' => $request->message
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $msg
        ]);
    }

    // 4️⃣ Get messages in a chat
    public function getMessages($chatId)
    {
        $chat = Chat::find($chatId);
        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $userId = Auth::id();
        if ($chat->user_one != $userId && $chat->user_two != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('chat_id', $chatId)
            ->with('sender:id,name,profile_image')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}
