<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends BaseController
{
    public function getChat(Request $request)
    {

        $sessionId = $request->input('session_id');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $messages = Message::where('session_id', $sessionId)
            ->where('customer_id', config('customer.id'))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        return $this->sendResponse(
            true,
            'Messages retrieved successfully',
            [
                'messages' => $messages,
                'offset' => $offset + $limit,
                'limit' => $limit,
            ],
            200
        );
    }
}
