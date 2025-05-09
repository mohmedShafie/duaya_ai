<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory , HasApiTokens;
   protected $guarded = [];

   public function faceEncoding()
   {
       return $this->hasOne(FaceEncoding::class);
   }

   public function vital()
   {
       return $this->hasMany(Vital::class);
   }
}
