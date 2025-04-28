<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/send_otp', [CustomerController::class, 'sendOTP']);
Route::post('verify_otp', [CustomerController::class, 'verifyOTP']);
Route::post('loginByFaceId', [CustomerController::class, 'loginByFaceId']);
Route::post('register', [CustomerController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('get_chat', [ChatController::class, 'getChat']);
    Route::get('get_sessions', [SessionController::class, 'getSessions']);
    Route::post('send_message', [MessageController::class, 'sendMessage']);
});
