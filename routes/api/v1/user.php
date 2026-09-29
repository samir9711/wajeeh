<?php



use App\Http\Controllers\Auth\UserAuthController;

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
Route::prefix('user')->name('user.')->group(function () {

    //Route::post('register', [UserAuthController::class, 'register'])->name('register');
    //Route::post('register/resend', [UserAuthController::class, 'resendRegisterOtp'])->name('register.resend');
    //Route::post('register/verify', [UserAuthController::class, 'verifyOtp'])->name('register.verify');
    // Login (email+pass | phone+pass | social_id)
    Route::post('login', [UserAuthController::class, 'login'])->name('login');
    Route::middleware('auth:user')->post('/device-token', [UserAuthController::class, 'syncDeviceToken']);
    Route::middleware('auth:user')->post('/me/profile', [UserController::class, 'updateProfile']);


    // Social register/login (upsert)
    //Route::post('social/register', [UserAuthController::class, 'socialRegister'])->name('social.register');

    // Reset password (email OR phone)
    //Route::post('password/send-otp', [UserAuthController::class, 'sendResetOtp'])->name('password.send_otp');
   // Route::post('password/verify-otp', [UserAuthController::class, 'verifyOtp'])->name('password.verify_otp'); //set the same method
    //Route::post('password/reset', [UserAuthController::class, 'resetPassword'])->name('password.reset');

    // Authenticated actions (Sanctum)
    Route::middleware('auth:user')->group(function () {
        //Route::post('resend-final-otp',       [UserAuthController::class, 'resendFinalOtp'])->name('resend_final_otp');
       // Route::post('request-phone-change', [UserAuthController::class, 'requestPhoneChange'])->name('phone.request_change');
       // Route::post('verify-phone-change-otp', [UserAuthController::class, 'verifyOtp'])->name('phone.verify_change'); //set the same method
       // Route::post('update-language', [UserAuthController::class, 'updateLanguage'])->name('language.update');
        Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');
    });


});
*/





