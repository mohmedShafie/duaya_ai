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
        ->offset($offset)
        ->limit($limit)
        ->get();
        $first_message = $sessions->messages->first();
        if ($first_message) {
            $first_message->message = $first_message->message;
        }
        return $this->sendResponse(
            true,
            'Sessions retrieved successfully',
            [
                'first_message' => $first_message,
                'sessions' => $sessions,
                'offset' => $offset + $limit,
                'limit' => $limit,
            ],
            200
        );
    }
}
