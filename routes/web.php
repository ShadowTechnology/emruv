<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Terms and Condition page for app

Route::get('/app/{content}','AdminController@viewContents');

Route::get('/','AdminController@index');

//Admin Route

Route::group(['prefix' => 'admin'], function () {

    Route::get('/','AdminController@index');

    Route::get('/login','AdminController@index');

    Route::post('/login','AdminController@postLogin');

    Route::get('/home','AdminController@homePage');

    Route::get('/logout','AdminController@logout');

    Route::post('/checkSetAdminCountry','AdminController@checkSetAdminCountry');

    // Settings

    Route::get('/settings','AdminController@settings');
    
    Route::post('/update/settings','AdminController@saveSettings');

    // Settings

    Route::get('/terms','AdminController@terms');

    Route::post('/save/terms','AdminController@saveTerms');

    Route::get('/about','AdminController@about');

    Route::post('/save/about','AdminController@saveAbout');

    Route::get('/ratecard','AdminController@ratecard');

    Route::post('/save/ratecard','AdminController@saveRatecard');

    // FAQ

    Route::get('/faq','AdminController@viewFAQ');

    Route::get('/faq/datatables',['as'=>'faqs.data','uses'=>'AdminController@getFAQ']);

    Route::post('/save/faq','AdminController@postFAQ');

    Route::post('/edit/faq','AdminController@editFAQ');

    //Countries

    Route::get('/countries','AdminController@viewCountries');

    Route::get('/countries/datatables',['as'=>'countries.data','uses'=>'AdminController@getCountries']);

    Route::post('/save/countries','AdminController@postCountries');

    Route::post('/edit/countries','AdminController@editCountries');

    //Languages

    Route::get('/languages','AdminController@viewLanguages');

    Route::get('/languages/datatables',['as'=>'languages.data','uses'=>'AdminController@getLanguages']);

    Route::post('/save/languages','AdminController@postLanguages');

    Route::post('/edit/languages','AdminController@editLanguages');

    //Banners

    Route::get('/banners','AdminController@viewBanners');

    Route::get('/banners/datatables',['as'=>'banners.data','uses'=>'AdminController@getBanners']);

    Route::post('/save/banners','AdminController@postBanners');

    Route::post('/edit/banners','AdminController@editBanners');

    // Slots

    Route::get('/slots','AdminController@viewSlots');

    Route::get('/slots/datatables',['as'=>'slots.data','uses'=>'AdminController@getSlots']);

    Route::post('/save/slots','AdminController@saveSlots');    

    Route::post('/edit/slots','AdminController@editSlots');

    // Zones

    Route::get('/zones','AdminController@viewZones');

    Route::get('/zones/datatables',['as'=>'zones.data','uses'=>'AdminController@getZones']);

    Route::post('/save/zones','AdminController@saveZones');    

    Route::post('/edit/zones','AdminController@editZones');

    // Banks

    Route::get('/banks','AdminController@viewBanks');

    Route::get('/banks/datatables',['as'=>'banks.data','uses'=>'AdminController@getBanks']);

    Route::post('/save/banks','AdminController@saveBanks');    

    Route::post('/edit/banks','AdminController@editBanks');

    // Days

    Route::get('/days','AdminController@viewDays');

    Route::get('/days/datatables',['as'=>'days.data','uses'=>'AdminController@getDays']);

    Route::post('/save/days','AdminController@saveDay');

    Route::post('/edit/days','AdminController@editDay');

    // Cancellation Policies

    Route::get('/cancellation_policy/add','AdminController@addNewCancellationPolicy');

    Route::post('/save/cancellation_policy','AdminController@saveCancellationPolicy');

    Route::get('/cancellation_policies','AdminController@viewCancellationPolicies');

    Route::get('/cancellation_policies/datatables',['as'=>'cancellation_policies.data','uses'=>'AdminController@getCancellationPolicies']);

    Route::get('/edit/cancellation_policy/{id}','AdminController@editCancellationPolicy');

    // Job Status

    Route::get('/job_status/add','AdminController@addNewJobStatus');

    Route::post('/save/job_status','AdminController@saveJobStatus');

    Route::get('/job_status','AdminController@viewJobStatus');

    Route::get('/job_status/datatables',['as'=>'job_status.data','uses'=>'AdminController@getJobStatus']);

    Route::get('/job_status/edit/{id}','AdminController@editJobStatus');

    // Fees Types

    Route::get('/fees_types/add','AdminController@addNewFeesTypes');

    Route::post('/save/fees_types','AdminController@saveFeesTypes');

    Route::get('/fees_types','AdminController@viewFeesTypes');

    Route::get('/fees_types/datatables',['as'=>'fees_types.data','uses'=>'AdminController@getFeesTypes']);

    Route::get('/edit/fees_types/{id}','AdminController@editFeesTypes');

    //Brands

    Route::get('/brands','AdminController@viewBrands');

    Route::get('/brands/datatables',['as'=>'brands.data','uses'=>'AdminController@getBrands']);

    Route::post('/save/brands','AdminController@postBrands');

    Route::post('/edit/brands','AdminController@editBrands');

    //Categories

    Route::get('/category/add','AdminController@addNewCategory');

    Route::post('/category/add','AdminController@saveCategory');

    Route::get('/categories','AdminController@viewCategories');

    Route::get('/categories/datatables',['as'=>'categories.data','uses'=>'AdminController@getCategories']);

    Route::get('/category/edit/{id}','AdminController@editCategory');

    Route::get('/categories_excel',['as'=>'categories_excel.data','uses'=>'AdminController@getCategoriesExcel']);  

    //Sub Categories

    Route::get('/subcategory/add','AdminController@addNewSubCategory');

    Route::post('/subcategory/add','AdminController@saveSubCategory');

    Route::get('/subcategories','AdminController@viewSubCategories');

    Route::get('/subcategories/datatables',['as'=>'subcategories.data','uses'=>'AdminController@getSubCategories']);

    Route::get('/subcategory/edit/{id}','AdminController@editSubCategory');

    Route::get('/subcategories_excel',['as'=>'subcategories_excel.data','uses'=>'AdminController@getSubCategoriesExcel']);  

    // Sub Category - Additional Instructions

    Route::get('/subcategory/instructions/{id}','AdminController@subcategoryInstructions');

    Route::get('/subcategory_instructions/datatables',['as'=>'subcategory_instructions.data','uses'=>'AdminController@getSubcategoryInstructions']);

    Route::post('/edit/subcategory_instruction','AdminController@editSubcategoryInstruction');

    Route::post('/add/subcategory_instruction','AdminController@addSubcategoryInstruction');

    Route::post('/update/subcategory_instruction','AdminController@updateSubcategoryInstruction');

    // Services

    Route::get('/service/add','AdminController@addNewService');

    Route::post('/service/add','AdminController@saveService');

    Route::get('/services','AdminController@viewServices');

    Route::get('/services/datatables',['as'=>'services.data','uses'=>'AdminController@getServices']);

    Route::get('/service/edit/{id}','AdminController@editService');

    Route::get('/services_excel',['as'=>'services_excel.data','uses'=>'AdminController@getServicesExcel']);  

    // Servicer Request Services

    Route::get('/provider_request_services','AdminController@viewOtherServices'); 

    Route::get('/provider_request_services/datatables',['as'=>'provider_request_services.data','uses'=>'AdminController@getOtherServices']);

    Route::post('/approveService','AdminController@approveOtherService');

    Route::post('/rejectService','AdminController@rejectOtherService');

    // Sub Services

    Route::post('/loadServices','AdminController@loadServices');

    Route::get('/subservice/add','AdminController@addNewSubService');

    Route::post('/subservice/add','AdminController@saveSubService');

    Route::get('/subservices','AdminController@viewSubServices');

    Route::get('/subservices/datatables',['as'=>'subservices.data','uses'=>'AdminController@getSubServices']);

    Route::get('/subservice/edit/{id}','AdminController@editSubService');

    Route::get('/subservices_excel',['as'=>'subservices_excel.data','uses'=>'AdminController@getSubServicesExcel']);  

    Route::post('/loadLevels','AdminController@loadLevels');
    
    // User Roles
    
    Route::get('/userroles','AdminController@viewUserRoles');
    
    Route::get('/userroles/datatables',['as'=>'userroles.data','uses'=>'AdminController@getUserRoles']);
    
    Route::post('/save/userroles','AdminController@postUserRoles');
    
    Route::post('/edit/userroles','AdminController@editUserRoles');

    Route::get('/role_excel/{option?}',['as'=>'role_excel.data','uses'=>'AdminController@getroleExcel']);  

    // Modules
    
    Route::get('/modules','AdminController@viewModules');

    Route::get('/modules/datatables',['as'=>'modules.data','uses'=>'AdminController@getModules']); 

    Route::post('/save/module','AdminController@postModule');    

    Route::post('/edit/module','AdminController@editModule');    

    // Role Module Mapping
    
    Route::get('/role_module_mapping','AdminController@viewRoleModuleMapping');

    Route::get('/user_roles/datatables',['as'=>'user_roles.data','uses'=>'AdminController@getUserRolesMapping']);

    Route::get('/role_module_mapping/datatables',['as'=>'role_mapping.data','uses'=>'AdminController@getRoleModuleMapping']);

    Route::post('/save/role_access','AdminController@postRoleModuleMapping');

    Route::get('/role_module_mapping/update_role_access/{id}','AdminController@ViewRoleAccess');
       
    Route::get('get_modules','AdminController@getModule');

    //Role Admin Users
    
    Route::get('/roleusers','AdminController@viewRoleUsers');
    
    Route::get('/roleusers/datatables',['as'=>'roleusers.data','uses'=>'AdminController@getRoleUsers']);
    
    Route::post('/save/roleusers','AdminController@postRoleUsers');
    
    Route::post('/edit/roleusers','AdminController@editRoleUsers');

    // Users

    Route::get('/users','AdminController@userPage');

    Route::get('/users/datatables',['as'=>'users.data','uses'=>'AdminController@getUsers']);

    Route::post('/save/user','AdminController@postUsers');

    Route::get('/users_excel',['as'=>'users_excel.data','uses'=>'AdminController@getUsersExcel']); 

    Route::get('/users/info/{id}/{code}','AdminController@viewProfile');
    Route::get('/users/edit/{id}/{code}','AdminController@editUser');
    Route::post('update/user','AdminController@putUser');

    Route::get('/users/bookings/{id}/{code}','AdminController@viewUserBookings');
    Route::get('/users/bookings/datatables/{id}/{code}',['as'=>'userbookings.data','uses'=>'AdminController@getUserBookings']);
    Route::get('/view/users/bookings/{id}/{uid}/{code}','AdminController@viewUserBookingDetail');
    Route::get('/view/users/bookings/{id}/{uid}/{code}','AdminController@viewNormalBookingDetail');

    // Servicers

    Route::get('/servicers/datatables',['as'=>'servicers.data','uses'=>'AdminController@getServicers']);

    Route::get('/servicers/info/{id}/{code}','AdminController@viewServicer');

    Route::get('/servicers/edit/{id}/{code}','AdminController@editServicer');

    Route::post('update/servicer','AdminController@putServicer');

    Route::post('approve/servicer','AdminController@putApproveServicer');

    Route::get('/servicers/bookings/{id}/{code}','AdminController@viewServicerBookings');
    Route::get('/servicers/bookings/datatables/{id}/{code}',['as'=>'servicerbookings.data','uses'=>'AdminController@getServicerBookings']);
    Route::get('/view/servicers/bookings/{id}/{uid}/{code}','AdminController@viewServicerBookingDetail');

    Route::get('servicers/{status?}','AdminController@servicerPage');

    // Bookings Details

    Route::get('/bookings','AdminController@viewBookings');
    Route::get('/bookings/datatables',['as'=>'bookings.data','uses'=>'AdminController@getBookings']);
    Route::get('/view/bookings/{id}/{code}','AdminController@viewBookingDetail');

    Route::get('/bookings_excel',['as'=>'bookings_excel.data','uses'=>'AdminController@getBookingsExcel']); 

    // Booking Payments
    Route::get('/booking_payments','AdminController@viewBookingPayments');
    Route::get('/booking_payments/datatables',['as'=>'booking_payments.data','uses'=>'AdminController@getBookingPayments']);
    Route::get('/booking_payments_excel',['as'=>'booking_payments_excel.data','uses'=>'AdminController@getBookingPaymentsExcel']); 
    
    Route::post('savepayout','AdminController@postSavePayout');

    // Payment Payouts
    Route::get('/payouts','AdminController@viewPayouts');
    Route::get('/payouts/datatables',['as'=>'payouts.data','uses'=>'AdminController@getPayouts']);
    Route::get('/payouts_excel',['as'=>'payouts_excel.data','uses'=>'AdminController@getPayoutsExcel']); 

    // User Wallet

    Route::get('/userwallet','AdminController@userWallet');
    Route::get('/userwallet/datatables',['as'=>'userwallet.data','uses'=>'AdminController@getUserWallet']);
    Route::get('/userwallet/excel',['as'=>'userwallet_excel_download.data','uses'=>'AdminController@getUserWalletExcel']);
    Route::post('/save/userwallet','AdminController@postUserWallet');
    Route::post('/user/get/walletamount','AdminController@postUserGetWalletAmount');

    // User Wallet Transactions

    Route::get('/userwallettransactions','AdminController@userWalletTransactions');
    Route::get('/userwallettransactions/datatables',['as'=>'userwallettransactions.data','uses'=>'AdminController@getUserWalletTransactions']);
    Route::get('/userwallettransactions/excel',['as'=>'userwallettransactions_excel_download.data','uses'=>'AdminController@getUserWalletTransactionsExcel']);

    // Zones Import

    Route::get('/excelimport','AdminController@excelimport');

    Route::post('/save/excelimport','ImportExportController@importExcelImport');
});





Route::get('payment-razorpay', 'PaymentController@create')->name('paywithrazorpay');
Route::post('payment', 'PaymentController@payment')->name('payment');


Route::post('/firebaseotpverify','ApiController@otpFireBaseVerification');

Route::get('/register','SiteController@userRegister');
