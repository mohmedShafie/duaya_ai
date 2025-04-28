<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends BaseController
{
    public function getSessions(Request $request)
    {
        $offset = request()->input('offset', 0);
        $limit = request()->input('limit', 10);

        $sessions = Session::where('customer_id', config('customer.id'))
            ->with('messages')
            ->offset($offset)
            ->limit($limit)
            ->get();


        $sessions = $sessions->map(function ($session) {
            $firstMessage = $session->messages->first();
            $session->first_message = $firstMessage;

            return $session;
        });

        return $this->sendResponse(
            true,
            'Sessions retrieved successfully',
            [
                'sessions' => $sessions,
                'offset' => $offset + $limit,
                'limit' => $limit,
            ],
            200
        );
    }
}
