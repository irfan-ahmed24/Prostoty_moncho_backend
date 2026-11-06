<?php

use App\Http\Controllers\User\UserController;
use App\Http\Middleware\SignupMiddleware;
use Illuminate\Support\Facades\Route;

// Status Check
Route::get('/status', [UserController::class, 'index']);

// Signup Route with Custom Middleware
Route::post('/register', [UserController::class, 'register'])
    ->middleware(SignupMiddleware::class);

// Login Route
Route::post('/login', [UserController::class, 'login']);

// User CRUD Routes
Route::get('/users', [UserController::class, 'getAllUsers']);

Route::get('/users/{id}', [UserController::class, 'getUserById']);

Route::put('/users/{id}', [UserController::class, 'update']);

Route::delete('/users/{id}', [UserController::class, 'destroy']);
