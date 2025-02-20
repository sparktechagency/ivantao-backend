<?php

use App\Http\Controllers\User\ConnectedAccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/account-success', [ConnectedAccountController::class, 'successAccount'])->name('stripe.success');

