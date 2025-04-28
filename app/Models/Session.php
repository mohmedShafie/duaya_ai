<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'created_at',
        'updated_at',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
