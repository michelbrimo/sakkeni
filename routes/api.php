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

Route::post('/admin-login', [AdminController::class, 'adminLogin'])->name('Admin.adminLogin');

Route::middleware('auth:admin')->group(function () {
    Route::get('/logout-admin', [AdminController::class, 'adminLogout'])->name('Admin.adminLogout'); 
    Route::middleware('superadmin')->group(function () {
        Route::post('/register-admin', [AdminController::class, 'adminRegister'])->name('Admin.adminRegister'); 
        Route::get('/view-admins', [AdminController::class, 'viewAdmins'])->name('Admin.viewAdmins');
        Route::get('/view-admin-profile/{admin_id}', [AdminController::class, 'viewAdminProfile'])->name('Admin.viewAdminProfile');
        Route::delete('/remove-admin/{admin_id}', [AdminController::class, 'removeAdmin'])->name('Admin.removeAdmin');
        Route::post('/search-admin', [AdminController::class, 'searchAdmin'])->name('Admin.searchAdmin'); 
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-profile', [UserController::class, 'viewMyProfile'])->name('User.viewUserProfile'); //done
    Route::post('/update-profile', [UserController::class, 'updateMyProfile'])->name('User.updateUserProfile');//done
    Route::get('/logout', [UserController::class, 'logout'])->name('User.logout');//done

    Route::post('/add-property', [PropertyController::class, 'addProperty'])->name('Property.addProperty');
    Route::get('/view-properties/{sell_type_id}', [PropertyController::class, 'viewProperties'])->name('Property.viewProperties');
    Route::post('/view-properties/{sell_type_id}', [PropertyController::class, 'filterProperties'])->name('Property.filterProperties');
    Route::get('/view-property-details/{property_id}', [PropertyController::class, 'viewPropertyDetails'])->name('Property.viewPropertyDetails');
    Route::get('/view-my-properties/{sell_type_id}', [PropertyController::class, 'viewMyProperties'])->name('Property.viewProperties');
    Route::post('/view-my-properties/{sell_type_id}', [PropertyController::class, 'filterMyProperties'])->name('Property.filterProperties');
    Route::delete('/delete-property/{property_id}', [PropertyController::class, 'deleteProperty'])->name('Property.deleteProperty');

    Route::get('/view-pending-properties', [PropertyController::class, 'viewPendingProperties'])->name('Property.viewPendingProperties');



    // View Default Data
    Route::get('/view-amenities', [PropertyController::class, 'viewAmenities'])->name('Property.viewAmenities');
    Route::get('/view-directions', [PropertyController::class, 'viewDirections'])->name('Property.viewDirections');
    Route::get('/view-property-types', [PropertyController::class, 'viewPropertyTypes'])->name('Property.viewPropertyTypes');
    Route::get('/view-commercial-property-types', [PropertyController::class, 'viewCommercialPropertyTypes'])->name('Property.viewCommercialPropertyTypes');
    Route::get('/view-residential-property-types', [PropertyController::class, 'viewResidentialPropertyTypes'])->name('Property.viewResidentialPropertyTypes');
    Route::get('/view-countries', [PropertyController::class, 'viewCountries'])->name('Property.viewCountries');


    Route::middleware('seller')->group(function () {
    });
});
