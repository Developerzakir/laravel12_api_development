<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('students',StudentController::class);

//register api
Route::post('/register',[AuthController::class,'register'])->name('register');
Route::post('/login',[AuthController::class,'login'])->name('login');

Route::group(['middleware'=>'auth:sanctum'], function(){
    Route::get('/profile',[AuthController::class,'profile'])->name('profile');
    Route::get('/logout',[AuthController::class,'logout'])->name('logout');

    // Blog Category Routes
    Route::apiResource('categories', CategoryController::class);
});
