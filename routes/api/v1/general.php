<?php


use App\Http\Controllers\Exercise\ExerciseController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Period\PeriodController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;


    Route::post('upload/{folder}/single', [UploadController::class, 'single'])
        ->where('folder', '[A-Za-z0-9_-]+');

    Route::post('upload/{folder}/multiple', [UploadController::class, 'multiple'])
        ->where('folder', '[A-Za-z0-9_-]+');

    Route::prefix('message')->group(function () {

        Route::post('/create',       [MessageController::class, 'store']);

    });








