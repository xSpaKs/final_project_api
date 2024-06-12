<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);

Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);
