<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
   protected $fillable = [
        'message',
        'audio',
        'file_path',
        'message_type',
        'session_id',
        'customer_id',
        'status',
    ];

    /**
     * Get the session that owns the chat.
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the customer that owns the chat.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
