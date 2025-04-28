<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'audio',
        'type',
        'session_id',
        'customer_id',
    ];

    /**
     * Get the session that owns this message.
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the customer that owns this message.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
