<?php


use App\Http\Controllers\AboutUs\AboutUsController;
use App\Http\Controllers\ContactDepartment\ContactDepartmentController;
use App\Http\Controllers\ContactInfo\ContactInfoController;
use App\Http\Controllers\Exercise\ExerciseController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Period\PeriodController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;


    Route::post('upload/{folder}/single', [UploadController::class, 'single'])
        ->where('folder', '[A-Za-z0-9_-]+');

    Route::post('upload/{folder}/multiple', [UploadController::class, 'multiple'])
        ->where('folder', '[A-Za-z0-9_-]+');

    Route::prefix('message')->group(function () {

        Route::post('/create',       [MessageController::class, 'store']);

    });

    Route::prefix('about-us')->group(function () {

        Route::post('/show',         [AboutUsController::class, 'show']);

    });

    Route::prefix('contact-info')->group(function () {

        Route::post('/show',         [ContactInfoController::class, 'show']);
        
    });

    Route::prefix('contact-department')->group(function () {
        Route::get('/all/paginated', [ContactDepartmentController::class, 'allPaginated']);
        Route::get('/all',           [ContactDepartmentController::class, 'all']);
        Route::post('/show',         [ContactDepartmentController::class, 'show']);

    });

    Route::prefix('product')->group(function () {
        Route::get('/all/paginated', [ProductController::class, 'allPaginated']);
        Route::get('/all',           [ProductController::class, 'all']);
        Route::post('/show',         [ProductController::class, 'show']);

    });








