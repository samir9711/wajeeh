<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
/*
// AboutUs PUBLIC ROUTES
Route::prefix('about-us')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\AboutUs\AboutUsController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\AboutUs\AboutUsController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\AboutUs\AboutUsController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\AboutUs\AboutUsController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\AboutUs\AboutUsController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\AboutUs\AboutUsController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\AboutUs\AboutUsController::class, 'deactivate']);
});

// Admin PUBLIC ROUTES
Route::prefix('admin')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Admin\AdminController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Admin\AdminController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Admin\AdminController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Admin\AdminController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Admin\AdminController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Admin\AdminController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Admin\AdminController::class, 'deactivate']);
});
*/
// ContactDepartment PUBLIC ROUTES
/*
Route::prefix('contact-department')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\ContactDepartment\ContactDepartmentController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\ContactDepartment\ContactDepartmentController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\ContactDepartment\ContactDepartmentController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\ContactDepartment\ContactDepartmentController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\ContactDepartment\ContactDepartmentController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\ContactDepartment\ContactDepartmentController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\ContactDepartment\ContactDepartmentController::class, 'deactivate']);
});

// ContactInfo PUBLIC ROUTES
Route::prefix('contact-info')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\ContactInfo\ContactInfoController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\ContactInfo\ContactInfoController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\ContactInfo\ContactInfoController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\ContactInfo\ContactInfoController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\ContactInfo\ContactInfoController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\ContactInfo\ContactInfoController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\ContactInfo\ContactInfoController::class, 'deactivate']);
});

// Product PUBLIC ROUTES
Route::prefix('product')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Product\ProductController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Product\ProductController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Product\ProductController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Product\ProductController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Product\ProductController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Product\ProductController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Product\ProductController::class, 'deactivate']);
});

// User PUBLIC ROUTES
Route::prefix('user')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\User\UserController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\User\UserController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\User\UserController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\User\UserController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\User\UserController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\User\UserController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\User\UserController::class, 'deactivate']);
});
*/

// Message PUBLIC ROUTES
