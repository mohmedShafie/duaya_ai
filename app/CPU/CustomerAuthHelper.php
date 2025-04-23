<?php
namespace App\CPU;

use Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CustomerAuthHelper
{

    public static function setConfig()
    {
        $customer = \Illuminate\Support\Facades\Auth::guard('sanctum')->user();

        if ($customer) {
            config()->set('customer.id', $customer->id);
            config()->set('customer.name', $customer->name);
            config()->set('customer.phone', $customer->phone);
        }
    }


}
