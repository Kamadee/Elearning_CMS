<?php

namespace App\Services;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use App\Mail\CustomerActiveMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerServices
{
    public function verify($code) {
        $customer = Customer::where("confirmation_code", $code)->first();
        if (!$customer) {
          return null; // Hoặc ném exception nếu cần
        }
        $customer->update([
          'status' => Config::get('constants.customer_status_enable'),
          'confirmation_code' => null,
          'email_verified_at' => Carbon::now(),
        ]);
        if($customer->email_verified_at) {
          throw new \Exception('Email already verified.');
        }
        return $customer;
    }
}
