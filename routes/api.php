<?php

use App\Http\Controllers\PropertyController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::post('/sign-up', [UserController::class, 'signUp'])->name('User.signUp');
Route::post('/login', [UserController::class, 'login'])->name('User.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-profile', [UserController::class, 'viewMyProfile'])->name('User.viewUserProfile');
    Route::post('/update-profile', [UserController::class, 'updateMyProfile'])->name('User.updateUserProfile');
    Route::get('/logout', [UserController::class, 'logout'])->name('User.logout');

    Route::post('/view-properties/{buy_type}/{property_type}', [PropertyController::class, 'viewProperties'])->name('Property.viewProperties');

    
    Route::post('/add-property', [PropertyController::class, 'addProperty'])->name('Property.addProperty');
    Route::middleware('seller')->group(function () {
    });
    

 Route::middleware('superadmin')->group(function () {
        Route::post('/register-admin', [AdminController::class, 'registerAdmin'])->name('Admin.registerAdmin');
        Route::get('/view-admins', [AdminController::class, 'viewAdmins'])->name('Admin.viewAdmins');
        Route::get('/view-admin-profile/{admin_id}', [AdminController::class, 'viewAdminProfile'])->name('Admin.viewAdminProfile');
        Route::delete('/remove-admin/{admin_id}', [AdminController::class, 'removeAdmin'])->name('Admin.removeAdmin');
    });
});
