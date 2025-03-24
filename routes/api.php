<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\NotificationController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Provider\DashboardProviderController;
use App\Http\Controllers\Provider\ExperienceController;
use App\Http\Controllers\Provider\ScheduleController;
use App\Http\Controllers\Provider\ServiceCategoryController;
use App\Http\Controllers\Provider\ServiceController;
use App\Http\Controllers\Provider\WithdrawController;
use App\Http\Controllers\SuperAdmin\ApplyFormController;
use App\Http\Controllers\SuperAdmin\CareerController;
use App\Http\Controllers\SuperAdmin\ContactUsController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\TransactionController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\User\CommunityForumController;
use App\Http\Controllers\User\CommunityForumReportController;
use App\Http\Controllers\User\ConnectedAccountController;
use App\Http\Controllers\User\ContactWithAdminController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ReportController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\SubscribeController;
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
     //uae pass login
     Route::post('uae-login',[AuthController::class,'socialLogin']);
    //subscribe
    Route::post('subscribe-join', [SubscribeController::class, 'subscribeJoin']);

    Route::middleware('auth:api')->group(function () {
        Route::get('own-profile', [AuthController::class, 'ownProfile']);
        Route::post('profile-update', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

});


//super admin
Route::middleware(['auth:api', 'super_admin'])->group(function () {
    //subscriber list
    Route::get('subscribe-list', [SubscribeController::class, 'subscribeList']);
    Route::delete('subscribe-delete/{id}', [SubscribeController::class, 'deleteSubscribe']);

    //dashboard
    Route::get('total-dashboard', [DashboardController::class, 'getDashboardStatistics']);
    //listing report
    Route::get('reportlist', [ReportController::class, 'reportlist']);
    Route::get('report-details/{id}', [ReportController::class, 'reportDetails']);
    Route::delete('report-delete/{id}', [ReportController::class, 'deleteReport']);

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
    Route::post('application-status/{id}', [ApplyFormController::class, 'updateApplicationStatus']);

    //forum report list
    Route::get('list-forum-report', [CommunityForumReportController::class, 'forumReportList']);
    Route::get('forum-report-details/{forum_id}', [CommunityForumReportController::class, 'forumReportDetails']);
    Route::delete('forum-delete/{id}', [CommunityForumController::class, 'deleteCommnityForum']);
    Route::delete('forum-report-delete/{id}', [CommunityForumReportController::class, 'deleteForumReport']);

    //about us and how it works add by super admin
    Route::post('create-setting', [SettingController::class, 'createSetting']);

    //create contact page
    Route::post('create-contact', [ContactUsController::class, 'createContact']);
    Route::post('contact-info', [ContactUsController::class, 'contactInfo']);

    //approved withdraw request
    Route::post('approve-withdraw/{withdrawId}', [WithdrawController::class, 'approveWithdraw']);

    //Transaction
    Route::get('transaction', [TransactionController::class, 'Transaction']);

    //notification
    Route::get('get-notify', [NotificationController::class, 'getnotification']);
    Route::get('read-notify/{id}', [NotificationController::class, 'readNotification']);
    Route::get('read-all-notify', [NotificationController::class, 'readAllNotification']);
    //notification for withdraw money
    Route::get('get-notify-withdraw', [NotificationController::class, 'WithdrawalNotify']);

});

//provider route
Route::middleware(['auth:api', 'provider'])->group(function () {



    //schedule
    Route::post('add-schedule', [ScheduleController::class, 'addSchedule']);
    Route::post('update-schedule/{id}', [ScheduleController::class, 'updateSchedule']);
    Route::get('list-schedule', [ScheduleController::class, 'getSchedule']);

    //experience
    Route::post('add-experience', [ExperienceController::class, 'addExperience']);
    Route::post('update-experience/{id}', [ExperienceController::class, 'updateExperience']);
    Route::get('get-experience', [ExperienceController::class, 'getExperiences']);
    Route::delete('delete-experience/{id}', [ExperienceController::class, 'deleteExperience']);

    //dashboard
    Route::get('dashboard', [DashboardProviderController::class, 'getDashboard']);

    //connected account
    Route::post('account-create', [ConnectedAccountController::class, 'createAccount'])->name('account-create');
    Route::get('account-refresh', [ConnectedAccountController::class, 'refreshAccount'])->name('account-refresh');
    Route::get('show-account', [ConnectedAccountController::class, 'showAccount'])->name('show-update');
    Route::delete('delete-accounts/{accountId}', [ConnectedAccountController::class, 'deleteAccount'])->name('account-delete');

    //withdraw money
    Route::post('request-withdraw', [WithdrawController::class, 'requestWithdraw']);
    Route::get('withdraw-money', [WithdrawController::class, 'getWithdrawMoney']);
    Route::get('withdraw-history', [WithdrawController::class, 'withdrawHistory']);
    Route::get('get-transaction', [TransactionController::class, 'TransactionforProvider']);

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

    //get order list
    Route::get('order-list', [OrderController::class, 'orderlist']);

    //notification
    Route::get('get-notification', [NotificationController::class, 'notification']);
    Route::get('mark-notification/{id}', [NotificationController::class, 'markNotification']);
    Route::get('mark-all-notification', [NotificationController::class, 'markAllNotification']);

    Route::get('order-list', [OrderController::class, 'orderlist']);
});

Route::middleware(['auth:api', 'user'])->group(function () {

    //review
    Route::post('reviews', [ReviewController::class, 'createReview']);

    //create report
    Route::post('report', [ReportController::class, 'report']);
    //forum report
    Route::post('forum-report', [CommunityForumReportController::class, 'forumReport']);

});


Route::middleware(['auth:api', 'user.admin.provider'])->group(function () {

    //message routes
    Route::post('send-message', [MessageController::class, 'sendMessage']);
    Route::get('get-message', [MessageController::class, 'getMessage']);
    Route::get('read-message', [MessageController::class, 'readMessage']);
    Route::get('search-user', [MessageController::class, 'searchUser']);
    Route::get('message-list', [MessageController::class, 'messageList']);

    //forum list
    Route::get('forum-list', [CommunityForumController::class, 'communityForumList']);

});



Route::middleware(['auth:api', 'user.provider'])->group(function () {
    //get profile for provider
    Route::get('provider-profile/{id}', [AuthController::class, 'providerProfile']);

    //apply form
    Route::post('apply-form', [ApplyFormController::class, 'applyForm']);

    //get all category and subcategory list
    Route::get('get-all-category', [ServiceCategoryController::class, 'getCategory']);
    Route::get('get-all-subcategory', [ServiceCategoryController::class, 'getSubCategory']);

    //get all services list and details
    Route::get('get-all-services', [ServiceController::class, 'getAllService']);
    Route::get('get-services-details/{id}', [ServiceController::class, 'servicesDetails']);

    //job list
    Route::get('list-job', [CareerController::class, 'jobList']);
    Route::get('job-details/{id}', [CareerController::class, 'careerDetails']);

    //forum post
    Route::post('forum-post', [CommunityForumController::class, 'forumPost']);

    //setting list
    Route::get('settings', [SettingController::class, 'settingList']);

    //contact get
    Route::get('contact-show', [ContactUsController::class, 'contactShow']);
    Route::post('contact-message', [ContactWithAdminController::class, 'contactMessage']);

    //order
    Route::post('order-payment', [OrderController::class, 'createPaymentIntent']);
    Route::post('create-order', [OrderController::class, 'paymentSuccess']);
    Route::get('order-details/{id}', [OrderController::class, 'orderDetails']);
    //when user and provider take service
    Route::get('order-list-user', [OrderController::class, 'orderlistUser']);

});


//before a register user show this
Route::get('get-all-category', [ServiceCategoryController::class, 'getCategory']);
Route::get('get-all-services', [ServiceController::class, 'getAllService']);
Route::get('get-services-details/{id}', [ServiceController::class, 'servicesDetails']);
Route::get('get-all-review', [ReviewController::class, 'reviewList']);



