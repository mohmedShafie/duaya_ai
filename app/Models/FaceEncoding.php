<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceEncoding extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'encoding' => 'binary',
    ];

    /**
     * Get the customer that owns the face encoding.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
