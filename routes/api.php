<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('countries','ApiController@getCountries');

// User Functionalities

Route::post('loginregister','ApiController@postUser');  

// Guest Login

Route::post('guestlogin','ApiController@postGuestUserLogin');

// OTP Verification

Route::post('otpverify','ApiController@otpVerification');

Route::post('otpresend','ApiController@resendOtp');

// send otp

Route::post('sendotp','ApiController@sendOtp');

// Update Profile

Route::post('user/updateprofile','ApiController@postUpdateUser');

Route::post('user/updateprofileimage','ApiController@postUpdateUserImage');

// Users Address

Route::post('/add/user/address','ApiController@postUserAddress');

Route::post('/update/user/address','ApiController@postUserAddress');

Route::post('/delete/user/address','ApiController@deleteUserAddress');

Route::post('/default/user/address','ApiController@defaultUserAddress');

Route::post('/show/user/address','ApiController@getUserAddress');

// Get Home Contents

Route::post('user/gethomecontents','ApiController@getHomeContents');

Route::post('user/getsubcategories','ApiController@getSubCategories');

Route::post('user/getservices','ApiController@getServices');

Route::post('user/getsubservices','ApiController@getSubServices');

Route::post('user/getsubcategoryservices','ApiController@getSubCategoryServices');

Route::post('user/gethomecategories','ApiController@getHomeCategories');

Route::post('user/gethomesubcategories','ApiController@getHomeSubCategories');

Route::post('user/getsubcatservices','ApiController@getSubCatServices');

Route::post('user/getsubserviceproducts','ApiController@getSubServiceProducts');

Route::post('user/addtocart','ApiController@getAddToCart');

Route::post('user/deletetocart','ApiController@getDeleteToCart');

Route::post('user/viewcart','ApiController@getViewCart');

Route::post('/user/clearcart','ApiController@clearCart');

Route::post('user/cartconfirmslot','ApiController@postConfirmSlotCart');

Route::post('user/getdayslist','ApiController@getDaysList');

Route::post('user/getdaySlotslist','ApiController@getDaySlotsList');

// User Search the Level 2 & 3 Categories

Route::post('/user/search','ApiController@getSearchCategories');

// Notifications

Route::post('notifications','ApiController@getNotifications');

// List of Slots

Route::post('allslots','ApiController@getAllSlots');

Route::post('slots','ApiController@getSlots');

// Brands

Route::post('brands','ApiController@getBrands');

// Faq

Route::post('userfaq','ApiController@getUserFaq');

Route::post('providerfaq','ApiController@getProviderFaq');

// Create General Booking

Route::post('insert_general_booking','ApiController@postInsertGeneralBooking');

Route::post('user_bookings','ApiController@getUserBookings');

Route::post('user_booking_detail','ApiController@getUserBookingDetails');

Route::post('/user_cancelbooking','ApiController@postUserBookingDecline');

// Reschedule servicer from user

Route::post('/user/getserviceravailabledays','ApiController@getUserServicerAvailableDays');

Route::post('/user/getserviceravailableslotsperday','ApiController@getUserServicerAvailableSlotsPerDay');

Route::post('/user/userrequestreschedule','ApiController@postUserRequestReschedule');

// User Make Payment for the Booking

Route::post('/user/makepayment','ApiController@postUserMakePayment');

// User Rate the Booking

Route::post('/user/rate_booking','ApiController@postUserRateBooking');

// User Contact Us Submission

Route::post('/user/contactus','ApiController@postUserContactUs');

// Zones List

Route::post('zones','ApiController@getZones');

// Banks List

Route::post('banks','ApiController@getBanks');

// Job Status

Route::post('/job_status','ApiController@getJobStatus');

// Rate Card

Route::post('/ratecard','ApiController@getRateCard');

// Servicer Functionalities

Route::post('servicerloginregister','ApiController@postServicer');   

Route::post('servicerUpdateStep1','ApiController@postServicerUpdateStep1');  // Name, email, address

Route::post('servicerUploadStep2','ApiController@postServicerUpdateStep2');  // id proof

Route::post('servicerUploadStep3','ApiController@postServicerUpdateStep3');  // Curent Address

Route::post('servicerUploadStep4','ApiController@postServicerUpdateStep4');  // Sub Category and Service Details

Route::post('servicerUploadServiceZone','ApiController@postServicerUploadServiceZone');  // Sub Category and Service Details

Route::post('servicerUploadOtherService','ApiController@postServicerUploadOtherService');  // Sub Category and Service Details

Route::post('servicerUploadStep5','ApiController@postServicerUploadStep5');  // Sub Category and Service Details

Route::post('servicerUpdateGST','ApiController@postServicerUpdateGST');  // Servicer GST Details

Route::post('servicerUpdatePAN','ApiController@postServicerUpdatePAN');  // Servicer PAN Details

Route::post('servicerUpdateBank','ApiController@postServicerUpdateBank');  // Servicer Bank Details

Route::post('servicerUpdateEmergencyContact','ApiController@postServicerUpdateEmergencyContact');  // Servicer Emergency Contact Details

Route::post('servicerUpdateProfileimage','ApiController@postServicerUpdateProfileImage');

Route::post('servicerUpdateLeave','ApiController@postServicerUpdateLeave');  // Servicer Leave Slot Details

Route::post('servicerViewProfile','ApiController@postServicerViewProfile');  // Servicer Leave Slot Details

Route::post('servicerLeaveSlots','ApiController@getServicerLeaveSlots');

Route::post('servicerAvailableSlots','ApiController@postServicerAvailableSlots');  // Servicer Available Slot Details

Route::post('servicerMainCategoryNames','ApiController@getServicerMainCategoryNames');  // Servicer Available Slot Details

Route::post('servicerNewLead','ApiController@getServicerNewLead');  // Servicer View the Latest Lead Details

Route::post('servicerLeadUpdateStatus','ApiController@getServicerLeadUpdateStatus');  // Servicer Update Status the Latest Lead Details

Route::post('servicerLeads','ApiController@getServicerLeads');  // Servicer Update Status the Latest Lead Details

Route::post('servicerViewBooking','ApiController@getServicerViewBooking');  // Servicer Update Status the Latest Lead Details

Route::post('/servicerStartJob','ApiController@postServicerJobStart');

Route::post('/servicerJobStatusUpdate','ApiController@postServicerJobStatusUpdate');

Route::post('/invoice/generate','ApiController@generateInvoice');

Route::post('/servicerEndjob','ApiController@postServicerJobEnd');

Route::post('/servicerRateBooking','ApiController@postServicerRateBooking');

Route::post('servicerBookings','ApiController@getServicerBookings');

Route::post('servicerDateBookings','ApiController@getServicerDateBookings');

// Servicer Booking Payments

Route::post('servicerBookingPayments','ApiController@getServicerBookingPayments');

Route::post('servicerBookingEarned','ApiController@getServicerBookingEarned');

