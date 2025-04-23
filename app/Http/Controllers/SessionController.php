<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function getSessions(Request $request)
    {

        $offset = request()->input('offset', 0);
        $limit = request()->input('limit', 10);
        $sessions = Session::where('customer_id', config('customer.id'))
        ->offset($offset)
        ->limit($limit)
        ->get();
        return response()->json([
            'status' => 'success',
            'sessions' => $sessions,
        ]);
    }
}
