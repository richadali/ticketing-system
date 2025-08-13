<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\BillsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Auth\AdminRegisterController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('auth.login');
});


// Override the default Auth::routes() to customize registration
Auth::routes(['register' => false]); // Disable default registration

// Custom admin registration routes
Route::get('/register-admin', [AdminRegisterController::class, 'showRegistrationForm'])
    ->middleware('guest')
    ->name('register-admin');

Route::post('/register-admin', [AdminRegisterController::class, 'register'])
    ->middleware('guest');

// OTP-based Password Reset Routes
Route::get('/password/reset-otp', [App\Http\Controllers\Auth\OtpPasswordResetController::class, 'showRequestForm'])
    ->middleware('guest')
    ->name('password.request-otp');

Route::post('/password/send-otp', [App\Http\Controllers\Auth\OtpPasswordResetController::class, 'sendOtp'])
    ->middleware('guest')
    ->name('password.send-otp');

Route::get('/password/verify-otp', [App\Http\Controllers\Auth\OtpPasswordResetController::class, 'showVerifyForm'])
    ->middleware('guest')
    ->name('password.verify-otp');

Route::post('/password/reset-with-otp', [App\Http\Controllers\Auth\OtpPasswordResetController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.reset-with-otp');

Route::post('/password/resend-otp', [App\Http\Controllers\Auth\OtpPasswordResetController::class, 'resendOtp'])
    ->middleware('guest')
    ->name('password.resend-otp');

// Route::get('/auth/google', [LoginController::class, 'redirectToProvider']);
// Route::get('/auth/google/callback', [LoginController::class, 'handleProviderCallback']);

Route::middleware('auth')->group(function () {


    Route::get('/home', [HomeController::class, 'index'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('home');

    // Commenting out problematic routes
    /*
    Route::post('view-data', [TableContoller::class, 'ViewContent'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('view-data');

    Route::post('store-data', [TableContoller::class, 'StoreData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('store-data');

    Route::post('show-data', [TableContoller::class, 'ShowData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('show-data');

    Route::post('delete-data', [TableContoller::class, 'DeleteData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('delete-data');

    Route::post('change-active', [TableContoller::class, 'ChangeActive'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('change-active');
    */

    //ADMIN ROUTES
    /*
    Route::get('/admin', [AdminController::class, 'index'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('admin');

    Route::post('admin-view-data', [AdminController::class, 'ViewContent'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('admin-view-data');

    Route::post('/admin-store-data', [AdminController::class, 'StoreData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('admin-store-data');

    Route::post('admin-show-data', [AdminController::class, 'ShowData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('admin-show-data');

    Route::post('admin-delete-data', [AdminController::class, 'DeleteData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('admin-data');
    */

    //USER ROUTES

    Route::get('/user', [UserController::class, 'index'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('user');

    Route::post('user-view-data', [UserController::class, 'ViewContent'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('user-view-data');

    Route::post('/user-store-data', [UserController::class, 'StoreData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('user-store-data');

    Route::post('user-show-data', [UserController::class, 'ShowData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('user-show-data');

    Route::post('user-delete-data', [UserController::class, 'DeleteData'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('user-data');

    // TICKETS ROUTES
    Route::get('/tickets', [TicketController::class, 'index'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.index');

    Route::get('/tickets/create', [TicketController::class, 'create'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.create');

    Route::post('/tickets', [TicketController::class, 'store'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.store');

    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.show');

    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.edit');

    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.update');

    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.destroy');

    Route::post('/tickets/{ticket}/assign-to-me', [TicketController::class, 'assignToMe'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.assign-to-me');

    Route::post('/tickets/{ticket}/assign-to-admin', [TicketController::class, 'assignToAdmin'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.assign-to-admin');

    Route::post('/tickets/{ticket}/change-status', [TicketController::class, 'changeStatus'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.change-status');
Route::post('/tickets/{ticket}/add-comment', [TicketController::class, 'addComment'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.add-comment');

    Route::get('/tickets-reports', [TicketController::class, 'reports'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('tickets.reports');

    // User Password Management
    Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('change-password');

    Route::post('/change-password', [UserController::class, 'changePassword'])
        ->middleware('cache.headers')
        ->middleware('throttle')
        ->name('change-password.update');
});

// Route::get('/read', function () {
//     return DB::SELECT('SELECT * from public.users');
// });

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
