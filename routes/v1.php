<?php

use Illuminate\Support\Facades\Route;

//get verification link
Route::post('email/verification-notification', 'Account\EmailVerificationController@sendVerificationEmail')
    ->middleware('throttle:6,1')
    ->name('verification.send');

// Auth
Route::post('auth/login', 'Auth\LoginController@index');

// get password reset link
Route::post('reset-password/get-link', 'Account\PasswordResetController@sendLinkByEmail')
    ->name('password.email');
// reset password API via link
Route::post('reset-password/reset', 'Account\PasswordResetController@resetPasswordViaLink')
    ->name('password.reset');
// check reset password link
Route::post('reset-password/verify-link', 'Account\PasswordResetController@checkLink');

// reset password API via otp
Route::post('reset-password/otp', 'Account\PasswordResetController@resetPasswordViaOTP');
// send or resend reset password via otp
Route::post('reset-password/send-OTP', 'Account\PasswordResetController@sendOTP');

Route::middleware('auth.v1:sca')->group(function () {
    // verify email
    Route::get('email/verify/{id}/{hash}', 'Account\EmailVerificationController@checkVerification')
        ->name('verification.verify');

    // Account
    Route::group(['namespace' => 'Account', 'prefix' => 'account'], function () {
        // Get me
        Route::get('me', 'MeController@index');
        //Edit me
        Route::put('me/edit', 'MeController@doEditMe');
    });

    // Admin
    Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {

        // CRUD user
        Route::post('user/add', 'AdminsController@doAdd');
        Route::get('user','AdminsController@getUsers');
        Route::get('user/{id}', 'AdminsController@getUser');
        Route::put('user/edit/{id}', 'AdminsController@doEdit');
        Route::delete('user/delete/{id}', 'AdminsController@deleteUser');
        //change password
        Route::put('user/changePassword', 'AdminsController@changePassword');
        // History login
        Route::get('loginHistory', 'SessionController@getSessions');
        // History Activity
        Route::get('activity', 'ActivityController@getActivities');
        // destroy session (far logout)
        Route::post('logout/{session_logout}', 'SessionController@destroySession');
        //destroy all session (logout all devices)
        Route::post('logoutAllDevices', 'SessionController@destroyAllSession');
    });
    //CUser
    Route::group(['namespace' => 'CUser', 'prefix' => 'admin'], function () {
        Route::get('ListCUser','CUserController@getUsers');
        Route::delete('CUser/delete/{id}','CUserController@deleteUser');
    });

    // logout
    Route::post('account/logout', 'Account\LogoutController@index');

});
