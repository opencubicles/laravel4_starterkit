<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::any('/', 'HomeController@showIndex');

# User Management
Route::get('admin/users', 'AdminUsersController@getIndex');
Route::get('admin/users/create', 'AdminUsersController@getCreate');
Route::post('admin/users/create', 'AdminUsersController@postCreate');
Route::get('admin/users/{userId}/edit', 'AdminUsersController@getEdit');
Route::post('admin/users/{userId}/edit', 'AdminUsersController@postEdit');
Route::get('admin/users/{userId}/delete', 'AdminUsersController@getDelete');

# Group Management
Route::get('admin/groups', 'AdminGroupsController@getIndex');
Route::get('admin/groups/create', 'AdminGroupsController@getCreate');
Route::post('admin/groups/create', 'AdminGroupsController@postCreate');
Route::get('admin/groups/{groupId}/edit', 'AdminGroupsController@getEdit');
Route::post('admin/groups/{groupId}/edit', 'AdminGroupsController@postEdit');
Route::get('admin/groups/{groupId}/delete', 'AdminGroupsController@getDelete');

# Admin Dashboard
Route::get('admin', 'AdminDashboardController@getIndex');

/*
|--------------------------------------------------------------------------
| Authentication and Authorization Routes
|--------------------------------------------------------------------------
*/

# Login
Route::get('account/login', 'AuthController@getLogin');
Route::post('account/login', 'AuthController@postLogin');

# Register
Route::get('account/register', 'AuthController@getRegister');
Route::post('account/register', 'AuthController@postRegister');

# Account Activation
Route::get('account/activate/{userID}/{activationCode}', 'AuthController@getActivate');

# Forgot Password
Route::get('account/forgot-password', 'AuthController@getForgotPassword');
Route::post('account/forgot-password', 'AuthController@postForgotPassword');

# Forgot Password Confirmation
Route::get('account/forgot-password/{userID}/{resetCode}', 'AuthController@getForgotPasswordConfirmation');
Route::post('account/forgot-password/{userID}/{resetCode}', 'AuthController@postForgotPasswordConfirmation');

# Logout
Route::get('account/logout', 'AuthController@getLogout');

# Settings
Route::get('account/settings', 'AccountSettingsController@getIndex');
Route::post('account/settings', 'AccountSettingsController@postIndex');

# Dashboard
Route::get('account', 'AccountDashboardController@getIndex');

# routes for handling social authentication via opauth 
Route::get('social/auth/{network?}', 'SocialController@auth');
Route::get('social/callback', 'SocialController@callback');

Route::get('social/auth/{network?}/int_callback', 'SocialController@auth');
Route::get('social/auth/{network?}/oauth2callback', 'SocialController@auth');
Route::get('social/auth/{network?}/oauth_callback', 'SocialController@auth');