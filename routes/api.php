<?php

use Illuminate\Support\Facades\Broadcast; 
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\QuoteController;

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PropertyController;

use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Models\Seller;
use App\Models\ServiceProvider;
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

Route::post('reset-password-mail', [PasswordController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');


Route::post('/sign-up', [UserController::class, 'signUp'])->name('User.signUp'); 
Route::post('/login', [UserController::class, 'login'])->name('User.login'); 
Route::post('/admin-login', [AdminController::class, 'adminLogin'])->name('Admin.adminLogin');


Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripeWebhook'])->name('stripe.handleWebhook');


Route::middleware('superadmin')->group(function () {
});


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
        Route::get('/view-pending-properties', [AdminController::class, 'viewPendingProperties'])->name('Admin.viewPendingProperties');
        Route::post('/property-adjudication', [AdminController::class, 'propertyAdjudication'])->name('Admin.propertyAdjudication');
        Route::get('/view-pending-service-providers', [AdminController::class, 'viewPendingServiceProviders'])->name('Admin.viewPendingServiceProviders');
        Route::post('/service-provider-service-adjudication', [AdminController::class, 'serviceProviderServiceAdjudication'])->name('Admin.serviceProviderServiceAdjudication');
        Route::post('/update-admin-profile', [AdminController::class, 'updateMyProfile'])->name('Admin.updateAdminProfile');

        Route::get('/reports/properties/{status}', [AdminController::class, 'viewPropertyReports'])->name('Report.viewPropertyReports');
        Route::get('/reports/service-providers/{status}', [AdminController::class, 'viewServiceProviderReports'])->name('Report.viewServiceProviderReports');
        Route::post('/reports/process-report/{id}', [AdminController::class, 'processReport'])->name('Report.processReport');
        
        Route::get('/charts/total-users', [DashboardController::class, 'viewTotalUsers'])->name('Dashboard.viewTotalUsers');
        Route::get('/charts/total-properties', [DashboardController::class, 'viewTotalProperties'])->name('Dashboard.viewTotalProperties');
        Route::get('/charts/properties-status', [DashboardController::class, 'viewPropertiesStatus'])->name('Dashboard.viewPropertiesStatus');
        Route::get('/charts/services-status', [DashboardController::class, 'viewServiceStatus'])->name('Dashboard.viewServiceStatus');
        Route::get('/charts/properties-locations', [DashboardController::class, 'viewPropertiesLocation'])->name('Dashboard.viewPropertiesLocation');
    });
});

Route::get('/view-properties/{sell_type}', [PropertyController::class, 'viewProperties'])->name('Property.viewProperties');
Route::post('/view-properties/{sell_type}', [PropertyController::class, 'filterProperties'])->name('Property.filterProperties');
Route::get('/view-property-details/{property_id}', [PropertyController::class, 'viewPropertyDetails'])->name('Property.viewPropertyDetails');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-profile', [UserController::class, 'viewMyProfile'])->name('User.viewUserProfile');
    Route::post('/update-profile', [UserController::class, 'updateMyProfile'])->name('User.updateUserProfile');
    Route::get('/logout', [UserController::class, 'logout'])->name('User.logout');
    Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('User.resetPassword');
    Route::post('/upgrade-to-seller', [UserController::class, 'upgradeToSeller'])->name('User.upgradeToSeller'); 
    Route::post('/upgrade-to-service-provider', [UserController::class, 'upgradeToServiceProvider'])->name('User.upgradeToServiceProvider'); 
    
    Route::get('/add-property-to-favorite/{property_id}', [PropertyController::class, 'addPropertyToFavorite'])->name('Property.addPropertyToFavorite');
    Route::get('/remove-property-from-favorite/{property_id}', [PropertyController::class, 'removePropertyFromFavorite'])->name('Property.removePropertyFromFavorite');
    Route::get('/view-favorite-properties/{sell_type}', [PropertyController::class, 'viewFavoriteProperties'])->name('Property.viewFavoriteProperties');
    
    Route::get('/view-best-service-providers', [ServiceProviderController::class, 'viewBestServiceProviders'])->name('ServiceProvider.viewBestServiceProviders'); 
    Route::get('/view-service-providers', [ServiceProviderController::class, 'viewServiceProviders'])->name('ServiceProvider.viewServiceProviders'); 
    Route::get('/view-service-provider-details/{service_provider_id}', [ServiceProviderController::class, 'viewServiceProviderDetails'])->name('ServiceProvider.viewServiceProviderDetails'); 
    Route::get('/view-service-provider-service-gallery/{service_provider_service_id}', [ServiceProviderController::class, 'viewServiceProviderServiceGallery'])->name('ServiceProvider.viewServiceProviderServiceGallery'); 
    
    Route::get('/view-recommended-properties', [PropertyController::class, 'showRecommendedProperties'])->name('Property.viewRecommendedProperties');
    
    Route::post('/report-property/{id}', [PropertyController::class, 'reportProperty'])->name('Report.reportProperty');
    Route::post('/report-service-provider/{id}', [ServiceProviderController::class, 'reportServiceProvider'])->name('Report.reportServiceProvider');
    
    Route::post('/upgrade-to-service-provider', [UserController::class, 'upgradeToServiceProvider'])->name('User.upgradeToServiceProvider');
    
    //done
    Route::post('/quotes/request', [QuoteController::class, 'requestQuote'])->name('Quote.requestQuote');
    Route::post('/quotes/{quote}/update-request', [QuoteController::class, 'updateQuoteRequest'])->name('Quote.updateQuoteRequest');
    Route::get('/provider/quotes', [QuoteController::class, 'viewProviderQuotes'])->name('Quote.viewProviderQuotes');
    Route::post('/quotes/{quote}/decline-user-quote', [QuoteController::class, 'declineUserQuote'])->name('Quote.declineUserQuote');

    Route::get('/conversations', [ConversationController::class, 'viewConversations'])->name('Conversation.viewConversations');
    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'viewMessages'])->name('Conversation.viewMessages');
    Route::post('/conversations/{conversation}/send-messages', [ConversationController::class, 'sendMessage'])->name('Conversation.sendMessage');


    //testing 
    Route::post('/quotes/{quote}/submit', [QuoteController::class, 'submitQuote'])->name('Quote.submitQuote'); 
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'acceptQuote'])->name('Quote.acceptQuote');
    Route::post('/quotes/{quote}/decline', [QuoteController::class, 'declineQuote'])->name('Quote.declineQuote');


    // --- PAYMENT ---
    Route::post('/service-activities/{serviceActivity}/pay', [PaymentController::class, 'createPaymentIntent'])->name('Payment.createPaymentIntent');

    // --- JOB COMPLETION ---
    // User confirms the job has been completed satisfactorily
    Route::post('/service-activities/{serviceActivity}/complete', [UserController::class, 'confirmCompletion'])->name('User.complete');



    Route::middleware('seller')->group(function () {
        Route::post('/add-property', [PropertyController::class, 'addProperty'])->name('Property.addProperty');
        Route::post('/update-property/{property_id}', [PropertyController::class, 'updateProperty'])->name('Property.updateProperty');
        Route::get('/view-my-properties/{sell_type}', [PropertyController::class, 'viewMyProperties'])->name('Property.viewProperties');
        Route::delete('/delete-property/{property_id}', [PropertyController::class, 'deleteProperty'])->name('Property.deleteProperty');
    });

    Route::middleware('serviceProvider')->group(function () {     
        Route::get('/view-my-services', [ServiceProviderController::class, 'viewMyServices'])->name('ServiceProvider.viewMyServices'); 
        Route::post('/add-service', [ServiceProviderController::class, 'addService'])->name('ServiceProvider.addService'); 
        Route::delete('/remove-service/{service_provider_service_id}', [ServiceProviderController::class, 'removeService'])->name('ServiceProvider.removeService'); 
        Route::post('/edit-service', [ServiceProviderController::class, 'editService'])->name('ServiceProvider.editService'); 
    });

    
    
    // View Default Data
    Route::get('/view-amenities', [PropertyController::class, 'viewAmenities'])->name('Property.viewAmenities');
    Route::get('/view-directions', [PropertyController::class, 'viewDirections'])->name('Property.viewDirections');
    Route::get('/view-property-types', [PropertyController::class, 'viewPropertyTypes'])->name('Property.viewPropertyTypes');
    Route::get('/view-commercial-property-types', [PropertyController::class, 'viewCommercialPropertyTypes'])->name('Property.viewCommercialPropertyTypes');
    Route::get('/view-residential-property-types', [PropertyController::class, 'viewResidentialPropertyTypes'])->name('Property.viewResidentialPropertyTypes');
    Route::get('/view-countries', [PropertyController::class, 'viewCountries'])->name('Property.viewCountries');
    Route::get('/view-availability-status', [PropertyController::class, 'viewAvailabilityStatus'])->name('Property.viewAvailabilityStatus');
    Route::get('/view-ownership-types', [PropertyController::class, 'viewOwnershipTypes'])->name('Property.viewOwnershipTypes');
    Route::get('/view-service-categories', [ServiceProviderController::class, 'viewServiceCategories'])->name('ServiceProvider.viewServiceCategories');
    Route::get('/view-subscription-plans', [ServiceProviderController::class, 'viewSubscriptionPlans'])->name('ServiceProvider.viewSubscriptionPlans');
    Route::get('/report-reasons/property', [PropertyController::class, 'viewPropertyReportReasons'])->name('Report.viewPropertyReportReasons');
    Route::get('/report-reasons/service-provider', [ServiceProviderController::class, 'viewServiceProviderReportReasons'])->name('Report.viewServiceProviderReportReasons');
    
   
});

Broadcast::routes();

