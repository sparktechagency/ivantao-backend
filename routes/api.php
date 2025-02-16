<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Provider\ServiceCategoryController;
use App\Http\Controllers\Provider\ServiceController;
use App\Http\Controllers\User\OfferPriceController;
use App\Http\Controllers\User\ReportController;
use App\Http\Controllers\User\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//auth route
Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify', [AuthController::class, 'verify']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::middleware('auth:api')->group(function () {
        Route::get('own-profile', [AuthController::class, 'ownProfile']);
        Route::post('profile-update', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

});
//super admin
Route::middleware(['auth:api', 'super_admin'])->group(function () {
    //listing report
    Route::get('reportlist', [ReportController::class, 'reportlist']);


});

//provider route
Route::middleware(['auth:api', 'provider'])->group(function () {

    //add category
    Route::get('get-all-category', [ServiceCategoryController::class, 'getCategory']);
    Route::get('get-all-subcategory', [ServiceCategoryController::class, 'getSubCategory']);
    Route::post('create-with-subcategory', [ServiceCategoryController::class, 'storeCategoryWithSubcategory']);
    Route::post('update-with-subcategory/{id}', [ServiceCategoryController::class, 'UpdateCategoryWithSubcategory']);
    Route::post('update-subcategory/{id}', [ServiceCategoryController::class, 'updateSubcategory']);
    Route::delete('delete-subcategory/{id}', [ServiceCategoryController::class, 'deleteSubcategory']);
    Route::delete('delete-category/{id}', [ServiceCategoryController::class, 'deleteServiceCategory']);

    //route for service
    Route::post('create-service', [ServiceController::class, 'createServices']);
    Route::post('update-service/{id}', [ServiceController::class, 'updateServices']);
    Route::delete('delete-service/{id}', [ServiceController::class, 'deleteService']);
    Route::get('get-all-services', [ServiceController::class, 'getService']);
    Route::get('get-services-details/{id}', [ServiceController::class, 'servicesDetails']);

    //route for service
    Route::post('/offer-price/{id}', [OfferPriceController::class, 'updateOfferStatus']);
    Route::get('/get-offer-price', [OfferPriceController::class, 'getOfferPrice']);

});

Route::middleware(['auth:api', 'user'])->group(function () {

    //review
    Route::post('reviews', [ReviewController::class, 'createReview']);
    Route::get('reviewlist', [ReviewController::class, 'reviewList']);
    //service offer
    Route::post('price-offer', [OfferPriceController::class, 'offerPrice']);
    //create report
    Route::post('report', [ReportController::class, 'report']);


});


