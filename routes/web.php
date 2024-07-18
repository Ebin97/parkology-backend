<?php

use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V2\SaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/receipt-rejection/{token}/{id}', [SaleController::class, 'rejectView'])->name('rejectView');
Route::post('/receipt-rejection/{id}', [SaleController::class, 'rejectPost'])->name('rejectPost');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::post('forget-password/{token}', [UserController::class, 'updatePassword'])->name('update.password');
Route::get('forget-password/{token}', [UserController::class, 'forgetPasswordPost'])->name('reset.password');

//require __DIR__.'/auth.php';
