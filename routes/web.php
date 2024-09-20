<?php

use App\Http\Controllers\FeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TopUpController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WalletController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/user/test/2', function () {
    User::find(2)->update([
        'role_id' => 2,
    ]);
});

Route::get('/user/test/1', function () {
    User::find(2)->update([
        'role_id' => 1,
    ]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::delete('/profile', function () {
    return dd('bapak mu');
})->middleware(['auth', 'verified'])->name('profile.destroy');
Route::put('payment/paymentSuccess', [PaymentController::class, 'paymentSuccess'])->middleware('auth')->name('payment.paymentSuccess');
Route::get('/profile', [ProfileController::class, 'edit'])->middleware('auth')->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
Route::post('payment/topUpUser', [PaymentController::class, 'topUpUser'])->middleware('auth')->name('payment.topUpUser');
Route::get('payment/topUpIndex', [PaymentController::class, 'topUpIndex'])->middleware('auth')->name('payment.topUpIndex');
Route::post('wallet/store', [WalletController::class, 'store'])->middleware('auth')->name('wallet.store');

Route::middleware(['auth', 'admin'])->group(function () {
    //User
    Route::get('user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
    Route::get('user/index', [UserController::class, 'index'])->name('user.index');
    Route::get('user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('user/store', [UserController::class, 'store'])->name('user.store');
    Route::put('user/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('user/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    
    //Transaction
    Route::get('transaction/index', [TransactionController::class, 'index'])->name('transaction.index');
    Route::post('transaction/store', [TransactionController::class, 'store'])->name('transaction.store');
    
    //Payment
    Route::get('payment/index', [PaymentController::class, 'index'])->name('payment.index');
    // Route::put('payment/update/{id}', [PaymentController::class, 'update'])->name('payment.update');
    
    //Top UP
    Route::get('topup/index', [TopUpController::class, 'index'])->name('topup.index');
    Route::post('topup/store', [TopUpController::class, 'store'])->name('topup.store');
    
    //Service
    Route::get('service/index', [ServiceController::class, 'index'])->name('service.index');
    Route::get('service/create', [ServiceController::class, 'create'])->name('service.create');
    Route::get('service/edit/{id}', [ServiceController::class, 'edit'])->name('service.edit');
    Route::post('service/store', [ServiceController::class, 'store'])->name('service.store');
    Route::put('service/update/{id}', [ServiceController::class, 'update'])->name('service.update');
    
    //Fee
    Route::get('fee/index', [FeeController::class, 'index'])->name('fee.index');
    Route::get('fee/create', [FeeController::class, 'create'])->name('fee.create');
    Route::get('fee/feeHistory', [FeeController::class, 'feeHistory'])->name('fee.feeHistory');
    Route::post('fee/store', [FeeController::class, 'store'])->name('fee.store');

    // Role
    Route::get('roles/index', [RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('roles/store', [RoleController::class, 'store'])->name('roles.store');
    Route::get('roles/edit/{id}', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('roles/update/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/destroy/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
});



require __DIR__.'/auth.php';
