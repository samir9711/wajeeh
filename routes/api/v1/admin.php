<?php



use App\Http\Controllers\AboutUs\AboutUsController;
use App\Http\Controllers\Auth\AdminAuthController;

use App\Http\Controllers\ContactDepartment\ContactDepartmentController;
use App\Http\Controllers\ContactInfo\ContactInfoController;
use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
    });

    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
    });


    Route::prefix('about-us')->middleware('auth:admin')->group(function () {

        Route::post('/show',         [AboutUsController::class, 'show']);
        Route::post('/create',       [AboutUsController::class, 'store']);

    });

    Route::prefix('contact-info')->middleware('auth:admin')->group(function () {

        Route::post('/show',         [ContactInfoController::class, 'show']);
        Route::post('/create',       [ContactInfoController::class, 'store']);

    });

    Route::prefix('contact-department')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [ContactDepartmentController::class, 'allPaginated']);
        Route::get('/all',           [ContactDepartmentController::class, 'all']);
        Route::post('/show',         [ContactDepartmentController::class, 'show']);
        Route::post('/create',       [ContactDepartmentController::class, 'store']);
        Route::post('/update',       [ContactDepartmentController::class, 'update']);
        Route::post('/activate',     [ContactDepartmentController::class, 'activate']);
        Route::post('/deactivate',   [ContactDepartmentController::class, 'deactivate']);
        Route::delete('/destroy',    [ContactDepartmentController::class, 'delete']);
    });

    Route::prefix('product')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [ProductController::class, 'allPaginated']);
        Route::get('/all',           [ProductController::class, 'all']);
        Route::post('/show',         [ProductController::class, 'show']);
        Route::post('/create',       [ProductController::class, 'store']);
        Route::post('/update',       [ProductController::class, 'update']);
        Route::post('/activate',     [ProductController::class, 'activate']);
        Route::post('/deactivate',   [ProductController::class, 'deactivate']);
        Route::delete('/destroy',    [ProductController::class, 'delete']);
    });




});

