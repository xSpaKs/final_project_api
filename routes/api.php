<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ UserController, SubscriptionController, EmailController, AuthController, StripeController, NewsController };

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);

Route::post("/mail-contact", [EmailController::class, 'sendEmailContactOwner']);

Route::post('/stripe/checkout', [StripeController::class, 'checkout'])->middleware('auth:sanctum');
Route::get('/stripe/subscriptions', [StripeController::class, 'subscriptions']);
Route::get('/subscriptions/{id}', [SubscriptionController::class, 'subscription']);

Route::get('/news', [NewsController::class, 'news']);
Route::get('/news/{id}', [NewsController::class, 'singleNews']);