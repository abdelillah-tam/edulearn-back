<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;



Route::get('/data', function (Request $request) {
    return response()->json([
        "data" => "testing data"
    ]);
})->middleware('auth:sanctum');

Route::middleware([StartSession::class])->group(function () {

    Route::middleware([VerifyCsrfToken::class])->group(function () {
        Route::post(
            '/signin',
            [UserController::class, 'signin']
        );

        Route::post('/signup', [UserController::class, 'signup']);

    });


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::post('/createCourse', [CourseController::class, 'createCourse']);
    });

    Route::get('/test', [UserController::class, 'test']);
});

Route::post('/getAllCourses', [CourseController::class, 'getAllCourses']);
Route::get('/getCategoryList', [CourseController::class, 'getCategoryList']);
Route::get('/getDifficultyList', [CourseController::class, 'getDifficultyList']);

Route::middleware('auth:sanctum')->group(function () {

});