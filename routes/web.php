<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/category/{category:slug}', [FrontController::class, 'category'])->name('front.category');
 
Route::get('/details/{house:slug}', [FrontController::class, 'details'])->name('front.details');
Route::get('/search', [FrontController::class, 'search'])->name('front.search');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:customer')->group(function () {
       Route::get('/dahsboard/mortgage/{mortgageRequest}/installment/payment', [DashboardController::class, 'installment_payment'])
       ->name('dashboard.installment.payment');

       Route::post('/dahsboard/mortgage/installment/payment', [DashboardController::class, 'paymentStorwMidtrans'])
       ->name('dashboard.installment.payment_store_midtrans');

       Route::get('/request/mortgage/{interest}', [FrontController::class, 'interest'])
       ->name('front.interest');

       Route::get('/request/mortgage/submitted', [FrontController::class, 'request_interest'])
       ->name('front.interest.submitted');

       Route::get('/request/mortgage/{interest}', [FrontController::class, 'interest'])
       ->name('front.interest');

       Route::get('/request/success', [FrontController::class, 'request_success'])
       ->name('front.request_success');

       Route::get('/dahsboard/mortgages/', [DashboardController::class, 'index'])
       ->name('dashboard');

       Route::get('/dahsboard/mortgages/{mortgageRequest}', [DashboardController::class, 'details'])
       ->name('dashboard.mortgage.details');

       Route::get('/dahsboard/mortgages/installment/{installment}', [DashboardController::class, 'installment_details'])
       ->name('dashboard.installment.details');

    });
});


require __DIR__.'/auth.php';
