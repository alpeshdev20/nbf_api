<?php

use App\Http\Controllers\AiConversion;
use App\Http\Controllers\Authentication;
use App\Http\Controllers\Consumers;
use App\Http\Controllers\Feedbacks;
use App\Http\Controllers\Payment;
use App\Http\Controllers\Resources;
use App\Http\Controllers\General;
use App\Http\Controllers\HelpCenter;
use App\Http\Controllers\Institutions;
use App\Http\Controllers\SocialAuth;
use App\Http\Controllers\SubscribeNewsletters;
use App\Http\Controllers\Subscription;
use App\Http\Controllers\UserStats;
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


//! Public Routes
Route::middleware('api-auth')->group(function () {

    //! Resources
    Route::prefix('resources')->group(function () {

        //* Books
        Route::get('/books/{start}/{limit}/{genre}/{age}', [Resources::class, 'get_books']);

        //* Audio Books
        Route::get('/audio-books/{start}/{limit}/{genre}/{age}', [Resources::class, 'get_audio_books']);

        //* Audio Books Rosource Info by id
        Route::get('/audio-books/{id}', [Resources::class, 'get_books']);

        //* Videos
        Route::get('/videos/{start}/{limit}/{genre}/{age}', [Resources::class, 'get_videos']);

        //* Class Notes
        Route::get('/class-notes/{start}/{limit}/{genre}/{age}', [Resources::class, 'get_class_notes']);

        //* Ar/Vr
        Route::get('/ar-vr/{start}/{limit}/{genre}/{age}', [Resources::class, 'get_ar_vr']);

        //* Gamified Learning
        Route::get('/gamified-learning/{start}/{limit}/{genre}/{age}', [Resources::class, 'get_gamified_learning']);

        //* Search Resources
        Route::get('/search/{key}/{category}/{age}', [Resources::class, 'search_resources']);
    });

    //! Authentication
    Route::prefix('auth/user')->group(function () {
        //* Create Acccount Normal users
        Route::post('/create-account', [Authentication::class, 'create_user_account']);

        //* Verify OTP for Normal users
        Route::post('/verify-otp', [Authentication::class, 'verify_user_otp']);

        //* Login
        Route::post('/authenticate-user', [Authentication::class, 'authenticate_user']);

        //* Forgot Password
        Route::post('/forgot-password', [Authentication::class, 'forgot_password']);

        //* Verify Forgot Password OTP
        Route::post('/verify-forgot-password-otp', [Authentication::class, 'verify_forgot_password_otp']);

        //* Change Password
        Route::post('/change-password', [Authentication::class, 'change_password']);

        Route::get('/social/{provider}', [SocialAuth::class, 'redirectToProvider']);
        Route::get('social/{provider}/callback', [SocialAuth::class, 'handleProviderCallback']);
    });


    //! Classes
    Route::get('/get-classes', [General::class, 'get_classes']);

    //! Age Groups
    Route::get('/age-groups', [General::class, 'get_age_groups']);

    //! Genres
    Route::get('/genres', [General::class, 'get_genres']);

    //! Material Categories
    Route::get('/resource-categories', [General::class, 'get_resource_categories']);

    //! Terms & Condition Page
    Route::get("/terms-conditions", [General::class, 'terms_condtions_page']);

    //! Privacy Policies Page
    Route::get("/privacy-policies", [General::class, 'privacy_policies_page']);

    //! FAQS Page
    Route::get("/faqs", [General::class, 'faqs_page']);


    //! Feedback
    Route::post('/feedback', [Feedbacks::class, 'feedback']);

    //! Query
    Route::post('/help-center', [HelpCenter::class, 'add_query']);

    //! New Letter
    Route::post('/subscribe-newsletter', [SubscribeNewsletters::class, 'subscribe_newsletter']);

    //! Institutions Registration
    Route::post('/register-institute', [Institutions::class, 'save_institute_info']);

    //! Register Publisher
    Route::post('/register-publisher', [Institutions::class, 'save_publisher_info']);

    Route::get('/subscription-plans', [Subscription::class, 'get_subscription_planS']);

    Route::get('/initiate-payment', [Payment::class, 'pay']);


    //! Institutions Registration
    Route::get('/publishers/{start}/{limit}', [Institutions::class, 'publisher_info']);

    //! api-auth Middleware end
});



//! Protected Routes
Route::middleware('api-auth-token')->group(function () {

    Route::prefix('user')->group(function () {
        //* Fetch Profile
        Route::get('/profile', [Consumers::class, 'user_info']);

        //* Update Profile
        Route::post('/profile/update', [Consumers::class, 'update_profile']);

        // cancel-subscription
        Route::post('/cancel-subscription/{id}', [Subscription::class, 'cancelSubscription']);

        //* Logout
        Route::post('/sign-out', [Consumers::class, 'sign_out']);

        //* Change Password
        Route::post('/change-password', [Consumers::class, 'change_password']);

        //* Wishlists
        Route::get('/wishlists', [Consumers::class, 'wishlists']);

        //* Library
        Route::get('/library', [Consumers::class, 'library']);
    });



    //! Resources
    Route::prefix('resources')->group(function () {
        //* Rosource Info by id
        Route::get('resources/{slug}', [Resources::class, 'get_resource_info']);


        //* Related Rosource
         Route::get('/related-resources/{slug}', [Resources::class, 'get_related_resources']);

        //* Rosource Episodes
        Route::get('/resource-episodes/{slug}', [Resources::class, 'get_resources_episodes']);

        //* Rosource Wishlist
        Route::post('/wishlist/{slug}', [Resources::class, 'wishlist']);
    });

    //! Verify Session
    Route::post('/verify-session', [Consumers::class, 'verify_session']);


    //! User Book reads.analytics
    Route::prefix('users-stats')->group(function () {
        //* Check is book ready by user
        Route::get('/is-book-read/{slug}', [UserStats::class, 'is_book_read']);

        //* Update book stats
        Route::post('/update-book-read-stats', [UserStats::class, 'update_book_read_stats']);


        //* Update book stats
        Route::post('/update-book-stats', [UserStats::class, 'update_book_stats']);
    });


    //! api-auth-token Middleware end
});




//! api-auth-token Middleware
Route::middleware('ai-api-auth')->group(function () {

    //! AI URL Routes
    Route::prefix('ai')->group(function () {
        //* Rosources for conversion
        Route::get('/resources-for-conversion/{limit}', [AiConversion::class, 'resouces_without_conversion']);

        //* Marked resource as converted
        Route::post('/marked-resource-converted', [AiConversion::class, 'marked_resource_as_converted']);
    });
});

Route::post('/paymentstatus', [Payment::class, 'paymentStatus']);

//! ai-api-auth-token Middleware end
