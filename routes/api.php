<?php

use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\TopUpController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::get('transaction/index', [TransactionController::class, 'index']);
Route::get('payment/index', [PaymentController::class, 'index']);
Route::get('user/index', [UserController::class, 'index']);
Route::get('service/index', [ServiceController::class, 'index']);
Route::post('webhook', [WebhookController::class, 'update']);
Route::get('payment/show/{id}', [PaymentController::class, 'show']);

Route::middleware(['auth:sanctum', 'walletStatus'])->group(function () {
    Route::get('user/show', [UserController::class, 'show']);
    Route::put('payment/paymentSuccess/{id}', [PaymentController::class, 'paymentSuccess']);
    Route::put('payment/paymentCancel/{id}', [PaymentController::class, 'paymentCancel']);
    Route::post('payment/store', [PaymentController::class, 'store']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('transaction/store', [TransactionController::class, 'store']);
    Route::get('transaction/history', [TransactionController::class, 'history']);
    Route::post('top/up/store', [TopUpController::class, 'store']);
});