<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
class CustomerController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:customers,phone',
            'full_name' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return $this->sendResponse(
                false,
                'Validation error',
                $validator->errors()->first(),
                422
            );
        }
        $customer = new Customer();
        $customer->phone = $request->phone;
        $customer->full_name = $request->full_name;
        $customer->suffix = $request->suffix;
        $customer->gender = $request->gender;
        $customer->is_pregnant = $request->is_pregnant ??0;
        $customer->is_breastfeeding = $request->is_breastfeeding ??0;
        $customer->birth_date = $request->birth_date;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->latitude = $request->latitude;
        $customer->longitude = $request->longitude;
        $customer->city = $request->city;
        $customer->state = $request->state;
        $customer->country = $request->country;
        $customer->save();
        return $this->sendResponse(
            true,
            'Customer registered successfully',
           ['user' => $customer],
            201
        );
    }
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(
                false,
                'Validation error',
                 $validator->errors(),
                422
            );
        }

        $mobile =  '+20'.$request->mobile;
        $existCustomer = Customer::where('phone' , $mobile)->first();

        // Handle test numbers
        if (in_array($mobile, ['+201142995709', '+201142995708' ,'+201234567890'])) {
            return $this->sendResponse(
                true,
                'OTP sent successfully',
                null,
                200
            );
        }

        try {
            $otp = rand(100000, 999999);

            Log::info('OTP: ' . $otp);

            // Store OTP with expiration time (1 minute)
            Cache::put('otp_' . $mobile, [
                'code' => $otp,
                'attempts' => 0,
                'created_at' => now()
            ], now()->addMinutes(1));

            // Send SMS using WhySMS
            $response = $this->sendWhySmsMessage($mobile, $otp , $request->country_code);

            if (!$response['success']) {
                return $this->sendResponse(
                    false,
                    $response['message'],
                    null,
                    $response['status_code']
                );
            }

            return $this->sendResponse(
                true,
                'OTP sent successfully',
                null,
                200
            );

        } catch (\Exception $e) {
            Log::error('OTP Send Error: ' . $e->getMessage());
            return $this->sendResponse(
                false,
                'Failed to send OTP. Please try again later.',
                null,
                500
            );
        }
    }

    private function sendWhySmsMessage($mobile, $otp , $code)
    {
        $client = new Client();
        $message = "Your OTP code is: {$otp}";

        try {
            $response = $client->post(config('services.whysms.url'), [
                'headers' => [
                    'Authorization' => "Bearer " . config('services.whysms.token'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'recipient' => $code.$mobile,
                    'sender_id' => config('services.whysms.sender_id'),
                    'type' => 'plain',
                    'message' => $message,
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            Log::info('WhySMS Response: ' . json_encode($responseBody));

            if (isset($responseBody['status']) && $responseBody['status'] == 'success') {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'status_code' => 200
                ];
            }

            return [
                'success' => false,
                'message' => 'SMS service is temporarily unavailable. Please try again later.',
                'status_code' => 429
            ];

        } catch (\Exception $e) {
            Log::error('WhySMS Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send SMS. Please try again later.',
                'status_code' => 500
            ];
        }
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string',
            'otp' => 'required|string|size:6',
            'fcm_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(
                false,
                'Validation error',
                $validator->errors(),
                422
            );
        }

        $mobile = $request->mobile;
        // Handle test numbers
        if (in_array($mobile, ['1142995709', '1142995708' , '1234567890']) && $request->otp === '123456') {
            return $this->handleTestNumberVerification($mobile);
        }

        // Verify actual OTP
        $verificationResult = $this->verifyOTPCode($mobile, $request->otp);
        if (!$verificationResult['success']) {
            return $this->sendResponse(
                false,
                $verificationResult['message'],
                null,
                $verificationResult['status_code']
            );
        }

        // Handle user authentication and registration
        return $this->handleUserAuthentication($mobile, $request->fcm_id);
    }

    private function handleTestNumberVerification($mobile)
    {
        $Customer = Customer::where('phone','LIKE', '%'.$mobile)->first();

        if (($Customer && $Customer->name == "NULL") || !$Customer) {
            return $this->sendResponse(
                true,
                'OTP verified successfully',
                [
                    'is_registered' => false,
                    'token' => null,
                ],
                200
            );
        }

        return $this->sendResponse(
            true,
            'OTP verified successfully',
            [
                'is_registered' => true,
                'token' => $Customer->createToken('auth_token')->plainTextToken,
                'user'=> $Customer,
            ],
            200
        );
    }

    private function verifyOTPCode($mobile, $otp)
    {
        $cachedData = Cache::get('otp_' . $mobile);

        if (!$cachedData) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'status_code' => 400
            ];
        }

        if ($cachedData['attempts'] >= 2) {
            Cache::forget('otp_' . $mobile);
            return [
                'success' => false,
                'message' => 'Too many invalid attempts. Please request a new OTP.',
                'status_code' => 400
            ];
        }

        if (now()->diffInMinutes($cachedData['created_at']) > 1) {
            Cache::forget('otp_' . $mobile);
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'status_code' => 400
            ];
        }
        Log::info('the cached otp is '.$cachedData['code']);
        if ($otp != $cachedData['code']) {
            $cachedData['attempts']++;
            Cache::put('otp_' . $mobile, $cachedData, now()->addMinutes(0.5));
            return [
                'success' => false,
                'message' => 'Invalid OTP',
                'status_code' => 400
            ];
        }

        Cache::forget('otp_' . $mobile);
        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'status_code' => 200
        ];
    }

    private function handleUserAuthentication($mobile, $fcm_id = null)
    {
        $Customer = Customer::where('phone', 'LIKE','%'.$mobile)->first();

        if ($Customer) {
            return $this->sendResponse(
                true,
                'OTP verified successfully',
                [
                    'is_registered' => false,
                    'token' => null
                ],
                200
            );
        }

        $token = $Customer->createToken('auth_token')->plainTextToken;

        if ($fcm_id) {
            // Usertokens::create([
            //     'fcm_id' => $fcm_id,
            //     'Customer_id' => $Customer->id
            // ]);
        }

        return $this->sendResponse(
            true,
            'OTP verified successfully',
            [
                'is_registered' => true,
                'user' => $Customer,
                'token' => $token
            ],
            200
        );
    }
}
