<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;



Route::get('/data', function (Request $request) {

    return response()->json([
        "env" => env('DB_HOST')
    ]);
});

Route::middleware([StartSession::class])->group(function () {


    Route::post('/signin', [UserController::class, 'signin']);

    Route::post('/signup', [UserController::class, 'signup']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/logout', [UserController::class, 'logout']);
        Route::post('/createCourse', [CourseController::class, 'createCourse']);
        Route::post('/enroll', [CourseController::class, 'enroll']);
        Route::get('/getCoursesEnrolled', [CourseController::class, 'getCoursesEnrolled']);
        Route::get('/getUser', [UserController::class, 'getUser']);
        Route::post('/setWatched', [CourseController::class, 'setWatched']);
        Route::get('/isInstructor', [UserController::class, 'isInstructor']);
        Route::get('/isStudent', [UserController::class, 'isStudent']);
        Route::get('/getInstructorCourses', [CourseController::class, 'getInstructorCourses']);
        Route::post('/signedIn', [UserController::class, 'isSignedIn']);
    });




    Route::get('/testPagination', [CourseController::class, 'testPagination']);
});

Route::post('/course/{course}', [CourseController::class, 'getCourse']);
Route::post('/getAllCourses', [CourseController::class, 'getAllCourses']);
Route::post('/getPopularCourses', [CourseController::class, 'popularCourses']);

Route::post('/prompting', [CourseController::class, 'prompting']);
