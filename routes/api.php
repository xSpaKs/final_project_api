<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ UserController, SubscriptionController, EmailController, AuthController, StripeController, NewsController, DiscountController, PaymentController };

Route::post('/user', [UserController::class, 'user'])->middleware('auth:sanctum');
Route::post('/modify-user', [UserController::class, 'modifyUser'])->middleware('auth:sanctum');
Route::post('delete-account', [UserController::class, 'deleteAccount'])->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post("/mail-contact", [EmailController::class, 'sendEmailContactOwner']);

Route::post('/stripe/checkout', [StripeController::class, 'checkout'])->middleware('auth:sanctum');
Route::post('/stripe/customer', [StripeController::class, 'customer'])->middleware('auth:sanctum');
Route::post('/stripe/webhook', [StripeController::class, "webhook"]);
Route::get('/stripe/subscriptions', [StripeController::class, 'subscriptions']);
Route::get('/stripe/subscriptions/{id}', [StripeController::class, 'subscription']);

Route::get('/news', [NewsController::class, 'news']);
Route::get('/news/{id}', [NewsController::class, 'singleNews']);

Route::post("/payments-from-user", [PaymentController::class, 'paymentsFromUser'])->middleware('auth:sanctum');