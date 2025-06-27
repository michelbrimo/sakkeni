<?php

use App\Http\Controllers\PropertyController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;


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
Route::get('/', function () {
    return view('welcome');
});


Route::post('forgot-password', [PasswordController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    })->middleware('guest')->name('password.reset');

Route::post('reset-password', [PasswordController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');




Route::post('/sign-up', [UserController::class, 'signUp'])->name('User.signUp'); 
Route::post('/login', [UserController::class, 'login'])->name('User.login'); 

Route::post('/admin-login', [AdminController::class, 'adminLogin'])->name('Admin.adminLogin');

Route::post('/register-admin', [AdminController::class, 'adminRegister'])->name('Admin.adminRegister'); 

Route::middleware('auth:admin')->group(function () {
    Route::get('/logout-admin', [AdminController::class, 'adminLogout'])->name('Admin.adminLogout'); 
    Route::middleware('superadmin')->group(function () {
        Route::get('/view-admins', [AdminController::class, 'viewAdmins'])->name('Admin.viewAdmins');
        Route::get('/view-admin-profile/{admin_id}', [AdminController::class, 'viewAdminProfile'])->name('Admin.viewAdminProfile');
        Route::delete('/remove-admin/{admin_id}', [AdminController::class, 'removeAdmin'])->name('Admin.removeAdmin');
        Route::post('/search-admin', [AdminController::class, 'searchAdmin'])->name('Admin.searchAdmin'); 
    });

    Route::middleware('admin')->group(function () {
        // Route::get('/view-pending-properties', [PropertyController::class, 'viewPendingProperties'])->name('Property.viewPendingProperties');
        // Route::post('/property-adjudication', [PropertyController::class, 'propertyAdjudication'])->name('Property.propertyAdjudication');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-profile', [UserController::class, 'viewMyProfile'])->name('User.viewUserProfile');
    Route::post('/update-profile', [UserController::class, 'updateMyProfile'])->name('User.updateUserProfile');
    Route::get('/logout', [UserController::class, 'logout'])->name('User.logout');






    Route::middleware('seller')->group(function () {
        // Route::post('/add-property', [PropertyController::class, 'addProperty'])->name('Property.addProperty');
        // Route::patch('/update-property/{property_id}', [PropertyController::class, 'updateProperty'])->name('Property.updateProperty');
        // Route::get('/view-my-properties/{sell_type}', [PropertyController::class, 'viewMyProperties'])->name('Property.viewProperties');
        // Route::delete('/delete-property/{property_id}', [PropertyController::class, 'deleteProperty'])->name('Property.deleteProperty');

    });

    Route::post('/add-property', [PropertyController::class, 'addProperty'])->name('Property.addProperty');
    Route::patch('/update-property/{property_id}', [PropertyController::class, 'updateProperty'])->name('Property.updateProperty');
    Route::get('/view-my-properties/{sell_type}', [PropertyController::class, 'viewMyProperties'])->name('Property.viewProperties');
    Route::delete('/delete-property/{property_id}', [PropertyController::class, 'deleteProperty'])->name('Property.deleteProperty');
    
    
    
    // up in the admin middleware
    Route::get('/view-pending-properties', [PropertyController::class, 'viewPendingProperties'])->name('Property.viewPendingProperties');
    Route::post('/property-adjudication', [PropertyController::class, 'propertyAdjudication'])->name('Property.propertyAdjudication');


    Route::get('/view-properties/{sell_type}', [PropertyController::class, 'viewProperties'])->name('Property.viewProperties');
    Route::post('/view-properties/{sell_type}', [PropertyController::class, 'filterProperties'])->name('Property.filterProperties');
    Route::get('/view-property-details/{property_id}', [PropertyController::class, 'viewPropertyDetails'])->name('Property.viewPropertyDetails');
    
    Route::get('/add-property-to-favorite/{property_id}', [PropertyController::class, 'addPropertyToFavorite'])->name('Property.addPropertyToFavorite');
    Route::get('/remove-property-from-favorite/{property_id}', [PropertyController::class, 'removePropertyFromFavorite'])->name('Property.removePropertyFromFavorite');
    Route::get('/view-favorite-properties/{sell_type}', [PropertyController::class, 'viewFavoriteProperties'])->name('Property.viewFavoriteProperties');
    

    // View Default Data
    Route::get('/view-amenities', [PropertyController::class, 'viewAmenities'])->name('Property.viewAmenities');
    Route::get('/view-directions', [PropertyController::class, 'viewDirections'])->name('Property.viewDirections');
    Route::get('/view-property-types', [PropertyController::class, 'viewPropertyTypes'])->name('Property.viewPropertyTypes');
    Route::get('/view-commercial-property-types', [PropertyController::class, 'viewCommercialPropertyTypes'])->name('Property.viewCommercialPropertyTypes');
    Route::get('/view-residential-property-types', [PropertyController::class, 'viewResidentialPropertyTypes'])->name('Property.viewResidentialPropertyTypes');
    Route::get('/view-countries', [PropertyController::class, 'viewCountries'])->name('Property.viewCountries');
    Route::get('/view-availability-status', [PropertyController::class, 'viewAvailabilityStatus'])->name('Property.viewAvailabilityStatus');
    Route::get('/view-ownership-types', [PropertyController::class, 'viewOwnershipTypes'])->name('Property.viewOwnershipTypes');


   
});
