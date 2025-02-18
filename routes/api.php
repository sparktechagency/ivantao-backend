<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Provider\ServiceCategoryController;
use App\Http\Controllers\Provider\ServiceController;
use App\Http\Controllers\SuperAdmin\ApplyFormController;
use App\Http\Controllers\SuperAdmin\CareerController;
use App\Http\Controllers\SuperAdmin\UserController;
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
    Route::get('report-details/{id}', [ReportController::class, 'reportDetails']);

    //get user list
    Route::get('user-list', [UserController::class, 'userList']);
    Route::get('user-details/{id}', [UserController::class, 'userDetails']);
    Route::delete('user-delete/{id}', [UserController::class, 'userDelete']);

    //get provider list
    Route::get('provider-list', [UserController::class, 'providerList']);
    Route::get('provider-details/{id}', [UserController::class, 'providerDetails']); //after order table i can create this result
    Route::delete('provider-delete/{id}', [UserController::class, 'providerDelete']);

    //career section
    Route::get('list-career', [CareerController::class, 'careerList']);
    Route::post('create-career', [CareerController::class, 'createCareer']);
    Route::post('update-career/{id}', [CareerController::class, 'updateCareer']);
    Route::delete('delete-career/{id}', [CareerController::class, 'deleteCareer']);

    //applied user list
    Route::get('list-applied-user/{career_id}', [ApplyFormController::class, 'appliedUsersList']);
    Route::get('details-applied-user/{id}', [ApplyFormController::class, 'appliedUsersDetails']);
    Route::post('/application-status/{id}', [ApplyFormController::class, 'updateApplicationStatus']);


});

//provider route
Route::middleware(['auth:api', 'provider'])->group(function () {

    //add category
    Route::post('create-with-subcategory', [ServiceCategoryController::class, 'storeCategoryWithSubcategory']);
    Route::post('update-with-subcategory/{id}', [ServiceCategoryController::class, 'UpdateCategoryWithSubcategory']);
    Route::post('update-subcategory/{id}', [ServiceCategoryController::class, 'updateSubcategory']);
    Route::delete('delete-subcategory/{id}', [ServiceCategoryController::class, 'deleteSubcategory']);
    Route::delete('delete-category/{id}', [ServiceCategoryController::class, 'deleteServiceCategory']);

    //route for service
    Route::post('create-service', [ServiceController::class, 'createServices']);
    Route::post('update-service/{id}', [ServiceController::class, 'updateServices']);
    Route::delete('delete-service/{id}', [ServiceController::class, 'deleteService']);

    //route for service
    Route::post('/offer-price/{id}', [OfferPriceController::class, 'updateOfferStatus']);
    Route::get('/get-offer-price', [OfferPriceController::class, 'getOfferPrice']);

});

Route::middleware(['auth:api', 'user'])->group(function () {

    //review
    Route::post('reviews', [ReviewController::class, 'createReview']);
    //service offer
    Route::post('price-offer', [OfferPriceController::class, 'offerPrice']);
    //create report
    Route::post('report', [ReportController::class, 'report']);

    //apply form
    Route::post('apply-form', [ApplyFormController::class, 'applyForm']);


});
Route::middleware(['auth:api', 'user.admin.provider'])->group(function () {

    //message routes
    Route::post('send-message', [MessageController::class, 'sendMessage']);
    Route::get('get-message', [MessageController::class, 'getMessage']);
    Route::get('read-message', [MessageController::class, 'readMessage']);
    Route::get('search-user', [MessageController::class, 'searchUser']);
    Route::get('message-list', [MessageController::class, 'messageList']);

});

Route::middleware(['auth:api', 'user.provider'])->group(function () {

    //get all category and subcategory list
    Route::get('get-all-category', [ServiceCategoryController::class, 'getCategory']);
    Route::get('get-all-subcategory', [ServiceCategoryController::class, 'getSubCategory']);

    //get all services list and details
    Route::get('get-all-services', [ServiceController::class, 'getAllService']);
    Route::get('get-services-details/{id}', [ServiceController::class, 'servicesDetails']);

    //job list
    Route::get('list-job', [CareerController::class, 'jobList']);
    Route::get('job-details/{id}', [CareerController::class, 'careerDetails']);

});
