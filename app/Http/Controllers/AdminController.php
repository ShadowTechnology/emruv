<?php

namespace App\Http\Controllers;

use App\Banner;
use App\Category;
use App\SubCategory;
use App\Services;
use App\SubServices;
use App\ServiceInstructions;
use App\Days;
use App\Faq;
use App\Languages;
use App\CancellationPolicies;
use App\JobStatus;
use App\User;
use App\Vendor;
use App\Countries;
use App\UserWallet;
use App\UserWalletDetail; 
use App\Slots;
use App\Zones;
use App\FeesTypes;
use App\Booking;
use App\ServiceProvider;
use App\Banks;
use App\ProviderPaymentDetails;
use App\UserRoles;
use App\Module;
use App\RoleModuleMapping;
use App\Warehouse;
use App\Brands;

use App\Jobs\UserUpdateEmailSender;

use Illuminate\Http\Request;
use App\Http\Controllers\CommonController; 
use Response;
use Log;
use DB;
use Input;
use Validator;
use Hash;
use Auth;
use Mail; 
use View;
use Session;
use Yajra\DataTables\DataTables;
use Excel;

class AdminController extends Controller
{
	/* Function: Index
       Loading Login page */
    public function index()    {  //echo time(); echo "<br>".Hash::make('123456');  echo "<br>".User::random_strings(60);
        return view('admin.login');
    }

    /* Function: postLogin
       Login Functionality
       Params: email , password
       return: JSON */

    public function postLogin(Request $request)    {

        $userEmail = $request->input('email');

        $password = $request->input('password');

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => $msg

            ]);
        }

        if (Auth::attempt(['email' => $userEmail, 'password' => $password])) { //, 'user_type' => 'SUPERADMIN'
 
            $userStatus = User::where('email', $userEmail)->first();

            if($userStatus->user_type == 'USER' || $userStatus->user_type == 'GUESTUSER' || $userStatus->user_type == 'SERVICEPROVIDER') {
                return response()->json([

                    'status' => "FAILED",
                    'message' => "Invalid Login"
                ]);
            }

            $userStatus->last_login_date = date('Y-m-d');

            $userStatus->save();

            $role_fk = UserRoles::where('ref_code', $userStatus->user_type)->value('id');
            session()->forget('module');
            $access = array();
            $role_access = RoleModuleMapping::where('ra_role_fk', $role_fk)->get();
            $i = 0; 
            foreach ($role_access as $m) {
                $module_id = $m['ra_module_fk'];

                $add = $m['ra_add'];
                $edit = $m['ra_edit'];
                $delete = $m['ra_delete'];
                $view = $m['ra_view'];

                $list = $m['ra_list'];
                $status_update = $m['ra_status_update'];
                $aadhar_status_update = $m['ra_aadhar_status_update'];

                $row_module = Module::find($module_id); 
                //$module_name = $row_module->url_name;
                $module_name = $row_module->url;
                $url = $row_module->url;

                if (! empty($url)) {
                    $access[$module_name] = array(
                        'aadhar_status_update' => $aadhar_status_update,
                        'status_update' => $status_update,
                        'list' => $list,
                        'add' => $add,
                        'edit' => $edit,
                        'delete' => $delete,
                        'view' => $view,
                        'url' => $url
                    );
                }
                $i ++;
            } 
            session()->put('role_fk', $role_fk);
            session()->put('module', $access);
            session()->put('user_type', $userStatus->user_type);

            return response()->json(['status' => 'SUCCESS', 'message' => 'Please wait redirecting...']);

        } else {

            return response()->json(['status' => 'FAILED', 'message' => 'Invalid Login Credential']);

        }

    }

    public function checkSetAdminCountry(Request $request)    {
        $session_country = $request->session_country;
        if($session_country>0) {
            $country_code = Countries::where('id', $session_country)->value('phonecode');
            Session::put('session_country', $session_country);
            Session::put('session_country_code', $country_code);
        }   else {
            Session::put('session_country', '');
            Session::put('session_country_code', '');
        }
        list($currency,$mrp_symbol) = CommonController::getAdminCurrency();

        Session::put('currency', $currency);
        Session::put('mrp_symbol', $mrp_symbol);
        
        return response()->json([

            'status' => "SUCCESS",
            'message' => 'Country set successfully'

        ]);
    }

    /* Function: homePage
       Loading Admin Home page */
    public function homePage(){
        if(Auth::check()){
            $session_country_code = Session::get('session_country_code');

            $totalBookings = $totalUsers = $totalServicers = $totalBookings = $pendingBookings = $completedBookings = 
                $canceledBookings = $paymentPendingBookings = $startedBookings = 
                $gst_commission = $revenue = $admin_commission = 0;

            $totalUsers = DB::table('users')->where('user_type', 'USER')->count();
            $totalServicers = DB::table('users')->where('user_type', 'SERVICEPROVIDER')->where('step', '>', 2)->count();

            // and created_at>="'.$from_date.'" and created_at<="'.$to_date.'"  GROUP BY user_source_from
            $wr = '';
            if(!empty($session_country_code)) {
                $wr = ' and country_code='.$session_country_code.' '; 
            }
            $activeuserscount = DB::select('SELECT count(id) as count, user_source_from  
                FROM users where user_type="USER" and status="ACTIVE"  and  api_token_expiry >="'.date('Y-m-d').' 00:00:00" '.$wr 
                  );

            if(!empty($activeuserscount)) {
                $activeuserscount = $activeuserscount[0]->count;
            }

            $activeproviderscount = DB::select('SELECT count(id) as count, user_source_from  
                FROM users where user_type="SERVICEPROVIDER" and status="ACTIVE"  and  api_token_expiry >="'.date('Y-m-d').' 00:00:00" '.$wr);

            if(!empty($activeproviderscount)) {
                $activeproviderscount = $activeproviderscount[0]->count;
            }

            $totalBookings = DB::table('em_booking')->count();
            $pendingBookings = DB::table('em_booking')->whereIN('status', ['PENDING'])->count();
            $completedBookings = DB::table('em_booking')->whereIN('status', ['COMPLETED', 'ENDED'])->count();
            $canceledBookings = DB::table('em_booking')->whereIN('status', ['CANCELLED'])->count();
            $paymentPendingBookings = DB::table('em_booking')->whereIN('status', ['COMPLETED', 'ENDED'])
                ->whereIN('payment_status', ['PENDING', 'PLACED'])->count();
            $startedBookings = DB::table('em_booking')->whereIN('status', ['COMPLETED', 'ENDED'])
                ->whereIN('status', ['ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR','RESCHEDULE_REQUEST','RESCHEDULED','UNABLETOCOMPLETE'])->count();
 
            $recent_bookings = DB::table('em_booking as b')
                ->leftjoin('users as u', 'u.id' , 'b.user_id')
                ->leftjoin('users as sp', 'sp.id' , 'b.service_provider_id')
                ->select('b.id', 'b.ref_no', 'b.user_id', 'b.service_provider_id', 'b.job_date', 'b.status', 'u.name', 'u.mobile', 'sp.name as providername', 'sp.mobile as providermobile', 'b.total_amount')
                ->orderby('id', 'desc')->take(10)->get();

            $gst_commission = DB::table('em_service_provider_payments')
                ->where('provider_settlement', 'PAID')
                ->sum('commission_percentage');

            $admin_commission = DB::table('em_service_provider_payments')
                ->sum('commission_percentage');

            $revenue = DB::table('em_service_provider_payments')
                ->sum('booking_amount');

            return view::make('admin.home',[
                'recent_bookings'=>$recent_bookings, 
                'totalBookings'=>$totalBookings, 
                'totalUsers'=>$totalUsers,
                'totalServicers'=>$totalServicers,
                'activeuserscount'=>$activeuserscount,
                'activeproviderscount'=>$activeproviderscount,
                'completedBookings'=>$completedBookings,
                'canceledBookings'=>$canceledBookings,
                'paymentPendingBookings'=>$paymentPendingBookings,
                'startedBookings'=>$startedBookings,
                'pendingBookings'=>$pendingBookings,
                'gst_commission' => $gst_commission,
                'revenue'=>$revenue,
                'admin_commission'=>$admin_commission
            ]);
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: logout
       Logout the session and redirects to login page */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            return redirect('/admin');
        } else {
            return redirect('/admin');
        }
    }

    //Countries
    /* Function: viewCountries
     */
    public function viewCountries()   {
        if(Auth::check()){
            return view('admin.countries');
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: getCountries
        Datatable Load
     */
    public function getCountries(Request $request)    {
        if(Auth::check()){
            $countries = Countries::all();
            return Datatables::of($countries)->make(true);
        }else{
            return redirect('/admin/login');
        } 
    }

    /* Function: postCountries
        Save into em_countries table
     */
    public function postCountries(Request $request)
    {
        if(Auth::check()){
            $id = $request->id;
            $code = $request->code;
            $phonecode = $request->phonecode;
            $name = $request->name;
            //$alias_name = $request->alias_name;
            $currency_symbol = $request->currency_symbol;
            $currency = $request->currency;
            //$alias_currency = $request->alias_currency;
            $position = $request->position;
            $status = $request->status;
            $image = $request->file('country_flag');

            $validator = Validator::make($request->all(), [
                'code' => 'required',
                'phonecode' => 'required',
                'name' => 'required',
                //'alias_name' => 'required',
                'currency_symbol' => 'required',
                'currency' => 'required',
                //'alias_currency' => 'required',
                'position' => 'required',
                'status' => 'required',
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            if($id > 0) {
                $country = Countries::find($id);
            }   else {
                $country = new Countries;
            }
            

            if (!empty($image)) {

                $countryimg = rand() . time() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('/image/countries');

                $image->move($destinationPath, $countryimg);

                $country->country_flag = $countryimg;

            }

            $country->code = $code;
            $country->phonecode = $phonecode;
            $country->name = $name;
            //$country->alias_name = $alias_name;
            $country->currency_symbol = $currency_symbol;
            $country->currency = $currency;
            //$country->alias_currency = $alias_currency;
            $country->position = $position;
            $country->status = $status;

            $country->save();
            return response()->json(['status' => 'SUCCESS', 'message' => 'Country Saved Successfully']);
        }else{
            return redirect('/admin/login');
        }
    }

    public function editCountries(Request $request)
    {
        if(Auth::check()){
            $country = Countries::where('id', $request->code)->get();
            if($country->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $country[0], 'message' => 'Country Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Country Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    //Brands
    /* Function: viewBrands
     */
    public function viewBrands()   {
        if(Auth::check()){
            return view('admin.brands');
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: getBrands
        Datatable Load
     */
    public function getBrands(Request $request)    {
        if(Auth::check()){
            $brands = Brands::all();
            return Datatables::of($brands)->make(true);
        }else{
            return redirect('/admin/login');
        } 
    }

    /* Function: postBrands
        Save into em_brands table
     */
    public function postBrands(Request $request)
    {
        if(Auth::check()){
            $id = $request->id;
            $brand_name = $request->brand_name;
            $position = $request->position;
            $status = $request->status;
            $image = $request->file('brand_image');

            $validator = Validator::make($request->all(), [
                'brand_name' => 'required', 
                'position' => 'required',
                'status' => 'required',
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            if($id > 0) {
                $brand = Brands::find($id);
            }   else {
                $brand = new Brands;
            } 

            if (!empty($image)) {

                $brandimg = rand() . time() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('/image/brands');

                $image->move($destinationPath, $brandimg);

                $brand->brand_image = $brandimg;

            }

            $brand->brand_name = $brand_name; 
            $brand->position = $position;
            $brand->status = $status;

            $brand->save();
            return response()->json(['status' => 'SUCCESS', 'message' => 'Brand Saved Successfully']);
        }else{
            return redirect('/admin/login');
        }
    }

    public function editBrands(Request $request)
    {
        if(Auth::check()){
            $brands = Brands::where('id', $request->code)->get();
            if($brands->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $brands[0], 'message' => 'Brand Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Brand Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    // Admin Settings
    public function settings()
    {
        if (Auth::check()) {
            $settings = DB::table('em_admin_settings')->orderby('id', 'asc')->first();
            $subcategories = SubCategory::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();
            return view('admin.settings')->with('settings', $settings)->with('subcategories', $subcategories);
        } else {
            return redirect('/admin/login');
        }
    }

    public function saveSettings(Request $request)
    {
        if (Auth::check()) {
            $site_on_off = $request->site_on_off;
            $def_pagination_limit = $request->def_pagination_limit;
            $def_expiry_after = $request->def_expiry_after;
            $helpcontact = $request->helpcontact;
            $admin_email = $request->admin_email;
            $contact_address = $request->contact_address;
            $hour_increment_by = $request->hour_increment_by;
            $admin_commission = $request->admin_commission;
            $gst_percentage = $request->gst_percentage;
            /*$booking_user_details_point_percentage = $request->booking_user_details_point_percentage; 
            $min_cutoff_points = $request->min_cutoff_points;*/
            $additional_charges = $request->additional_charges;
            $additional_charge_text = $request->additional_charge_text;
            $category_ids  = $request->category_ids;
            $max_count_zone  = $request->max_count_zone;
            $max_count_subcategory  = $request->max_count_subcategory;
              
            $categories = '';
            if(is_array($category_ids)) {
                $categories = implode(',', $category_ids);
            }
            DB::table('em_admin_settings')->where('id', 1)->update([
                'site_on_off' => $site_on_off,
                'def_pagination_limit' => $def_pagination_limit,
                'def_expiry_after' => $def_expiry_after,
                'helpcontact' => $helpcontact,
                'admin_email' => $admin_email,
                'contact_address' => $contact_address,
                'hour_increment_by' => $hour_increment_by,
                'admin_commission' => $admin_commission,
                'gst_percentage' => $gst_percentage,
                /*'booking_user_details_point_percentage' => $booking_user_details_point_percentage, 
                'min_cutoff_points' => $min_cutoff_points,*/
                'additional_charges' => $additional_charges,
                'additional_charge_text' => $additional_charge_text,
                'additional_category_ids' => $categories,
                'max_count_zone' => $max_count_zone,
                'max_count_subcategory' => $max_count_subcategory,
            ]);

            return response()->json([

                'status' => "SUCCESS",
                'message' => "Saved Successfully"
            ]);
        } else {
            return response()->json([

                'status' => "FAILED",
                'message' => "Session Logged Out. Please Login Again"
            ]);
        }
    }

    // Terms and Conditions
    public function terms() {
        if(Auth::check()){
            $settings = DB::table('em_admin_settings')->orderby('id', 'asc')->first();
            $user_terms_conditions = $settings->user_terms_conditions;
            $servicer_terms_conditions = $settings->servicer_terms_conditions; 
            return view('admin.terms')->with('user_terms_conditions', $user_terms_conditions)
            ->with('servicer_terms_conditions', $servicer_terms_conditions);
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveTerms(Request $request) {
        if(Auth::check()){
            $user_terms_conditions = $request->user_terms_conditions;
            $servicer_terms_conditions = $request->servicer_terms_conditions; 

            DB::table('em_admin_settings')->where('id', 1)
                ->update(['user_terms_conditions'=>$user_terms_conditions, 
                    'servicer_terms_conditions'=>$servicer_terms_conditions
            ]);

            return response()->json([

                'status' => "SUCCESS",
                'message' => "Terms and Conditions Saved Successfully"
            ]);
        }else{
            return response()->json([

                'status' => "FAILED",
                'message' => "Session Logged Out. Please Login Again"
            ]);
        }
    }

    // Rate Card
    public function ratecard() {
        if(Auth::check()){
            $settings = DB::table('em_admin_settings')->orderby('id', 'asc')->first();
            $ratecard = $settings->ratecard; 
            return view('admin.ratecard')->with('ratecard', $ratecard);
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveRatecard(Request $request) {
        if(Auth::check()){
            
            $image = $request->file('ratecard');
            if (!empty($image)) {

                $ratecardimg = rand() . time() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('/image/ratecard');

                $image->move($destinationPath, $ratecardimg);
  
                DB::table('em_admin_settings')->where('id', 1)
                    ->update(['ratecard'=>$ratecardimg,  
                ]);

            }            

            return response()->json([

                'status' => "SUCCESS",
                'message' => "Ratecard Saved Successfully"
            ]);
        }else{
            return response()->json([

                'status' => "FAILED",
                'message' => "Session Logged Out. Please Login Again"
            ]);
        }
    }


    // About HurryBunny
    public function about() {
        if(Auth::check()){
            $settings = DB::table('em_admin_settings')->orderby('id', 'asc')->first();
            $about = $settings->about; 
            return view('admin.about')->with('about', $about);
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveAbout(Request $request) {
        if(Auth::check()){
            $about = $request->about; 
            DB::table('em_admin_settings')->where('id', 1)
                ->update(['about'=>$about]);

            return response()->json([

                'status' => "SUCCESS",
                'message' => "About Info Saved Successfully"
            ]);
        }else{
            return response()->json([

                'status' => "FAILED",
                'message' => "Session Logged Out. Please Login Again"
            ]);
        }
    }

    // FAQs
    /* Function: viewFAQ
     */
    public function viewFAQ()   {
        if(Auth::check()){
            return view('admin.faq');
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: getFAQ
        Datatable Load
     */
    public function getFAQ(Request $request)    {
        if(Auth::check()){
            $faq = Faq::all();
            return Datatables::of($faq)->make(true);
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: postFAQ
        Save into em_faq table
     */
    public function postFAQ(Request $request)
    {
        if(Auth::check()){
            $id = $request->id;
            $faq_for = $request->faq_for;
            $faq_type = $request->faq_type;
            $question = $request->question;
            //$alias_question = $request->alias_question;
            $answer = $request->answer;
           // $alias_answer = $request->alias_answer;
            $position = $request->position;
            $status = $request->status;

            $validator = Validator::make($request->all(), [
                'faq_for' => 'required',
                'faq_type' => 'required',
                'question' => 'required',
               // 'alias_question' => 'required',
                'answer' => 'required',
                //'alias_answer' => 'required',
                'position' => 'required',
                'status' => 'required',
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            if($id > 0) {
                $faq = Faq::find($id);
                $faq->updated_at = date('Y-m-d H:i:s');
            }   else {
                $faq = new Faq;
                $faq->created_at = date('Y-m-d H:i:s');
            }
            
            $faq->faq_for = $faq_for;
            $faq->faq_type = $faq_type;
            $faq->question = $question;
          //  $faq->alias_question = $alias_question;
            $faq->answer = $answer;
          //  $faq->alias_answer = $alias_answer;
            $faq->position = $position;
            $faq->status = $status;

            $faq->save();
            return response()->json(['status' => 'SUCCESS', 'message' => 'Faq Saved Successfully']);
        }else{
            return redirect('/admin/login');
        }
    }

    public function editFAQ(Request $request)
    {
        if(Auth::check()){
            $faq = Faq::where('id', $request->code)->get();
            if($faq->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $faq[0], 'message' => 'Faq Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Faq Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }


    //Languages
    /* Function: viewLanguages
     */
    public function viewLanguages()   {
        if(Auth::check()){
            return view('admin.languages');
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: getLanguages
        Datatable Load
     */
    public function getLanguages(Request $request)    {
        if(Auth::check()){
            $languages = Languages::all();
            return Datatables::of($languages)->make(true);
        }else{
            return redirect('/admin/login');
        } 
    }

    /* Function: postLanguages
        Save into em_language table
     */
    public function postLanguages(Request $request)
    {
        if(Auth::check()){
            $id = $request->id;
            $code = $request->code; 
            $language = $request->language; 
            $position = $request->position;
            $by_default = $request->by_default;
            $status = $request->status;

            $validator = Validator::make($request->all(), [
                'code' => 'required',
                'language' => 'required',
                'by_default' => 'required',
                'position' => 'required',
                'status' => 'required',
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            if($id > 0) {
                $languages = Languages::find($id);
            }   else {
                $languages = new Languages;
            }

            $languages->code = $code;
            $languages->language = $language;
            $languages->position = $position; 
            $languages->by_default = $by_default;  
            $languages->status = $status;

            $languages->save();
            return response()->json(['status' => 'SUCCESS', 'message' => 'Language Saved Successfully']);
        }else{
            return redirect('/admin/login');
        }
    }

    public function editLanguages(Request $request)
    {
        if(Auth::check()){
            $languages = Languages::where('id', $request->code)->get();
            if($languages->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $languages[0], 'message' => 'Language Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Language Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    //Banner

     public function viewBanners()    {
        if(Auth::check()){
        return view('admin.banner');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getBanners(Request $request)    {

        $banners = Banner::all();
        return Datatables::of($banners)->make(true);

    } 

    public function postBanners(Request $request)
    {   if(Auth::check()){

        $id = $request->id;

        $name = $request->name;

        $status = $request->status;

        $position = $request->position;

        $is_link = $request->is_link;

        $link_level = $request->link_level;

        $link_id = $request->link_id;       
        
        $image = $request->file('image');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs"
            ]);
        }

        if($is_link == 'YES') {
            if(empty($link_level) || empty($link_id)) {
                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }            
        }

        if($id>0) {
            $banner = Banner::find($id);
        }   else {
            $banner = new Banner;
        }

        if (!empty($image)) {

            $bannerimg = rand() . time() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('/image/banner');

            $image->move($destinationPath, $bannerimg);

            $banner->banner_image =  $bannerimg;

        }

        $banner->name = $name;

        $banner->status = $status;

        $banner->position = $position;

        $banner->is_link = $is_link;
        $link_level = 0;
        $link_id = 0;
        $category_id = 0;
        if($is_link == 'NO') {
            $link_level = 0;
            $link_id = 0;
            $category_id = 0;
        }   else if($is_link == 'YES') {
            $category_id = 0;
            if($link_level == 2) {
                $category_id = DB::table('em_sub_category')->where('id', $link_id)->value('category_id');
            }   else if($link_level == 3)   {
                $category_id = DB::table('em_sub_cat_services')->where('id', $link_id)->value('sub_category_id');
            }
        }

        $banner->category_id = $category_id;

        $banner->link_level = $link_level;

        $banner->link_id = $link_id;

        $banner->save();
            
        return response()->json(['status' => 'SUCCESS', 'message' => 'Banner has been saved'], 201);
        
        }else{
            return redirect('/admin/login');
        }
    }

    public function editBanners(Request $request)
    {
        if(Auth::check()){
            $banner = Banner::where('id', $request->code)->get();
            if($banner->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $banner[0], 'message' => 'Banner Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Banner Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    // Load Levels

    public function loadLevels(Request $request) {
        $link_level = $request->link_level;
        $link_id = $request->link_id;
        $levels = '';
        if($link_level == 2) {
            $levels = DB::table('em_sub_category')->select('id', 'name')->where('status', 'ACTIVE')->get();
            $leveloption = '<option value="">Select Sub Category</option>';
        }   else if($link_level == 3) {
            $levels = DB::table('em_sub_cat_services')->select('id', 'name')->where('status', 'ACTIVE')->get();
            $leveloption = '<option value="">Select Service</option>';
        }  else {
            $leveloption = '<option value="">Select</option>';
        } 

        
        if(!empty($levels) && $levels->isNotEmpty()) {
            foreach ($levels as $key => $value) {
                if($link_id == $value->id) {
                    $selected = ' selected ';
                } else {
                    $selected = '';
                }
                $leveloption .= '<option value="'.$value->id.'" '.$selected.'>'.$value->name.'</option>';
            }
        }
        return response()->json(['status' => 'SUCCESS', 'message' => 'Levels List', 'data'=>$leveloption]);
    }

    // Slots

    public function viewSlots(){
        if(Auth::check()){
        return view('admin.slots');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getSlots(){

        $slots = Slots::select('em_slots.*', DB::RAW('SUBSTR(ADDTIME(from_time, CONVERT(replace(check_hours, ".", ":"), TIME)), 1, 5) as from_time'))->get();

        return Datatables::of($slots)->make(true);
    }
 
    public function saveSlots(Request $request)
    {

        $id = $request->id;

        $slot_name = $request->slot_name;

        $from_time = $request->from_time;

        $to_time = $request->to_time;

        $period_name = $request->period_name;

        $status = $request->status;

        $position = $request->position;

        $check_hours = $request->check_hours;

        $check_limit = $request->check_limit;

        $validator = Validator::make($request->all(), [
            'slot_name' => 'required',
            'from_time' => 'required',
            'to_time' => 'required',
            'period_name' => 'required',
            'status' => 'required',
            'position' => 'required',
            'check_hours' => 'required',
            'check_limit' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        $timestamp = strtotime($from_time);
        $selected_to_time = strtotime($to_time);

        /*$slots = DB::table('em_slots')->get();
        if(!empty($slots)) {
            foreach ($slots as $slot) {  
                $slot_from_time = strtotime($slot->from_time);
                $slot_to_time = strtotime($slot->to_time);


                if(($timestamp >= $slot_from_time && $timestamp <= $slot_to_time) || ($selected_to_time >= $slot_from_time && $selected_to_time <= $slot_to_time) ) {
                    return response()->json(['status' => 'FAILED', 'message' => 'The Slot must not collapse with any other Time slots'], 201);

                    /*'The Slot must not collapse with any other Time slots'.$fromtime.">=".$slot->from_time."&&".$fromtime."<=".$slot->to_time."||".$totime.">=".$slot->from_time."&&".$totime."<=".$slot->to_time* /
                }
            }
        }*/

        if($id>0) {
            $slot = Slots::find($id);
        }   else {
            $slot = new Slots;
        }

        $slot->slot_name = $slot_name;

        $slot->from_time = $from_time;

        $slot->to_time = $to_time;

        $slot->period_name = $period_name;

        $slot->status = $status;

        $slot->position = $position;   

        $checkhours = ($check_hours)>0 ? $check_hours : 0;
        $checklimit = ($check_limit)>0 ? $check_limit : 0;

        $timestamp = strtotime($from_time);
        $time = $timestamp - ($checkhours * 60 * 60);
        $fromtime = date("H:i", $time);

        $slot->from_time = $fromtime;
        $slot->counts_per_slot = $checklimit;
        $slot->check_hours = $checkhours;

        $slot->save();


        return response()->json(['status' => 'SUCCESS', 'message' => 'Slot has been Saved'], 201);

    }    

    public function editSlots(Request $request)
    {
        if(Auth::check()){
            $slots = Slots::where('id', $request->code)->first();

            $slots = collect(\DB::select('SELECT *, SUBSTR(ADDTIME(from_time, CONVERT(replace(check_hours, ".", ":"), TIME)), 1, 5) as from_time from em_slots where id='.$request->code))->first();

            if(!empty($slots)) {
                return response()->json(['status' => 'SUCCESS', 'data' => $slots, 'message' => 'Slot Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Slot Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    // Zones

    public function viewZones(){
        if(Auth::check()){
        return view('admin.zone');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getZones(){

        $zones = Zones::all();

        return Datatables::of($zones)->make(true);
    }
 
    public function saveZones(Request $request)
    {

        $id = $request->id;

        $zone_name = $request->zone_name; 

        $status = $request->status;

        $position = $request->position;

        $validator = Validator::make($request->all(), [
            'zone_name' => 'required',
            'status' => 'required',
            'position' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }
 
        if($id>0) {
            $zone = Zones::find($id);
        }   else {
            $zone = new Zones;
        }

        $zone->zone_name = $zone_name; 

        $zone->status = $status;

        $zone->position = $position;

        $zone->save();

        return response()->json(['status' => 'SUCCESS', 'message' => 'Zone has been Saved'], 201);

    }    

    public function editZones(Request $request)
    {
        if(Auth::check()){
            $zone = Zones::where('id', $request->code)->get();
            if($zone->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $zone[0], 'message' => 'Zone Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Zone Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    // Banks

    public function viewBanks(){
        if(Auth::check()){
        return view('admin.banks');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getBanks(){

        $banks = Banks::all();

        return Datatables::of($banks)->make(true);
    }
 
    public function saveBanks(Request $request)
    {

        $id = $request->id;

        $bank_name = $request->bank_name; 

        $status = $request->status;

        $position = $request->position;

        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'status' => 'required',
            'position' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        if($id>0) {
            $exists = DB::table('em_banks')->where('bank_name', $bank_name)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_banks')->where('bank_name', $bank_name)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Bank Name Already Exists'], 201);
        }  
 
        if($id>0) {
            $banks = Banks::find($id);
        }   else {
            $banks = new Banks;
        }

        $banks->bank_name = $bank_name; 

        $banks->status = $status;

        $banks->position = $position;

        $banks->save();

        return response()->json(['status' => 'SUCCESS', 'message' => 'Bank has been Saved'], 201);

    }    

    public function editBanks(Request $request)
    {
        if(Auth::check()){
            $banks = Banks::where('id', $request->code)->get();
            if($banks->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $banks[0], 'message' => 'Bank Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Bank Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    // Categories

    public function viewCategories(){
        if(Auth::check()){
        return view('admin.categories');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getCategories(){

        $categories = Category::all();

        return Datatables::of($categories)->make(true);
    }

    public function getCategoriesExcel(Request $request)    { 
        if (Auth::check()) {
            
            $input = $request->all();
            $start = $input['start'];
            $length = $input['length'];

            $input = $request->all(); 
            $columns = $request->get('columns'); 
        
            $categoriesqry = Category::where('id', '>', 0);
            
            if (count($columns) > 0) {
                foreach ($columns as $key => $value) {
                    if (! empty($value['data']) && ! empty($value['search']['value'])) {
                        $categoriesqry->where($value['data'], 'like', '%' . $value['search']['value'] . '%');
                    }
                }
            }
            $categories = $categoriesqry->select('em_category.*')->get();
          
            $categories_excel = [];
            if (! empty($categories)) {
                foreach ($categories as $rev) {
                    
                    $created_date = $rev->created_at;
                    $my_date = strtotime($created_date);
                    $created_date = date("Y-m-d h:i:a", $my_date);

                    $categories_excel[] = [
                        "Name" => $rev->name,
                        "Tax Percent" => $rev->tax_percent,
                        "Position" => $rev->position,
                        "Home Display" => $rev->home_display,
                        "Status" => $rev->status,
                    ];
                }
            }

            header("Content-Type: text/plain");
            $flag = false;
            foreach ($categories_excel as $row) {
                if (! $flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            exit();
          
        } else {
            return redirect('/admin/login');
        }
    }

    public function addNewCategory(){
        if(Auth::check()){
            $languages = [];
            $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
            return view('admin.categories_add')->with('languages', $languages);
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveCategory(Request $request)
    {

        $id = $request->id;

        $name = $request->name;

        $type = $request->type;

        $tax_percent = $request->tax_percent;

        $description = $request->description;   

        $home_display = $request->home_display;

        $position = $request->position;

        $status = $request->status;

        $image = $request->file('image');

        $input = $request->all();
        //echo "<pre>"; print_r($input); exit;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'tax_percent' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs"
            ]);
        }

        if($id>0) {
            $exists = DB::table('em_category')->where('name', $name)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_category')->where('name', $name)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Category Already Exists'], 201);
        }  

        if($id>0) {
            $category = Category::find($id);
            $category->updated_at = date('Y-m-d H:i:s');
        }   else {
            $category = new Category;
            $category->created_at = date('Y-m-d H:i:s');
        }

        if (!empty($image)) {

            $categoryImage = 'category-' .rand().time() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/categories');

            $image->move($destinationPath, $categoryImage);

            $category->image = $categoryImage;

        }

        $category->type = $type;

        $category->name = $name;

        $category->tax_percent = $tax_percent;

        $category->description = $description;

        $category->home_display = $home_display;

        $category->position = $position;

        $category->status = $status;

        $category->save();

        $name_lang = $request->name_lang;

        $description_lang = $request->description_lang;

        if(isset($name_lang) && count($name_lang)>0) {
            foreach ($name_lang as $language_id => $value) {
                $exists_lang = DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$category->id,
                             'level'=>1])->first();

                if(!empty($exists_lang)) {
                    DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$category->id,
                             'level'=>1])
                    ->update([
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }   else {
                    DB::table('em_category_language')->insert([
                        'language_id'=>$language_id,
                        'category_id'=>$category->id,
                        'level'=>1,
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }


        return response()->json(['status' => 'SUCCESS', 'message' => 'Category has been Saved'], 201);

    }    

    public function editCategory($id)
    {
        if(Auth::check()){
        $category = Category::where('id', $id)->first();
        $languages = [];
        $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
        $language_content = DB::table('em_category_language')
                    ->where(['category_id'=>$id, 'level'=>1])->get();  
        $lang_arr = [];
        if(count($language_content)>0) {
            foreach ($language_content as $key => $value) {
                $lang_arr[$value->language_id]['title'] = $value->title;
                $lang_arr[$value->language_id]['description'] = $value->description;
            }
        }

            return view('admin.categories_add')->with('category', $category)
                ->with('languages', $languages)
                ->with('language_content', $lang_arr);
        }else{
            return redirect('/admin/login');
        }
    }

    // Sub Categories

    public function viewSubCategories(){
        if(Auth::check()){
        return view('admin.sub_categories');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getSubCategories(Request $request){

        $input = $request->all();
        $start = $input['start'];
        $length = $input['length'];

        $input = $request->all(); 
        $columns = $request->get('columns'); 
    
        $categoriesqry =  SubCategory::leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
        ->select('em_sub_category.*', 'em_category.name as category_name');
        $filteredqry = SubCategory::leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
        ->select('em_sub_category.*', 'em_category.name as category_name');
        
        if (count($columns) > 0) {
            foreach ($columns as $key => $value) {
                if (! empty($value['name']) && ! empty($value['search']['value'])) {
                    $categoriesqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    $filteredqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                }
            }
        }
        $categories = $categoriesqry->skip($start)->take($length)->get();
        $filters = $filteredqry->select('em_sub_category.id')->count();  

        $totalData = SubCategory::leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
            ->select('em_sub_category.id')->count();  

        $totalFiltered = $totalData;   
        if(!empty($filters)) {
            $totalFiltered = $filters;
        }  
        
        $data = [];
        if(!empty($categories))    {
            foreach ($categories as $post)
            {  
                $data[] = $post;
            }
        }
        

         $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 

     //   return Datatables::of($categories)->make(true);
    }

    public function getSubCategoriesExcel(Request $request)    { 
        if (Auth::check()) {
            
            $input = $request->all();
            $start = $input['start'];
            $length = $input['length'];

            $input = $request->all(); 
            $columns = $request->get('columns'); 
        
            $categoriesqry = SubCategory::leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
            ->select('em_sub_category.*', 'em_category.name as category_name');
            
            if (count($columns) > 0) {
                foreach ($columns as $key => $value) {
                    if (! empty($value['name']) && ! empty($value['search']['value'])) {
                        $categoriesqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    }
                }
            }
            $categories = $categoriesqry->get();
          
            $categories_excel = [];
            if (! empty($categories)) {
                foreach ($categories as $rev) {
                    
                    $created_date = $rev->created_at;
                    $my_date = strtotime($created_date);
                    $created_date = date("Y-m-d h:i:a", $my_date);

                    $categories_excel[] = [
                        "Category" => $rev->category_name,
                        "Name" => $rev->name,
                        "Commission Percent" => $rev->commission_percentage,
                        "Position" => $rev->position,
                        "Ratecard" => $rev->ratecard,
                        "Videolink" => $rev->video_link,
                        "Status" => $rev->status,
                    ];
                }
            }

            header("Content-Type: text/plain");
            $flag = false;
            foreach ($categories_excel as $row) {
                if (! $flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            exit();
          
        } else {
            return redirect('/admin/login');
        }
    }

    public function addNewSubCategory(){
        if(Auth::check()){
        $category = Category::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();
        $languages = [];
        $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
        return view('admin.sub_categories_add')->with('category', $category)->with('languages', $languages);
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveSubCategory(Request $request)
    {

        $id = $request->id;

        $category_id = $request->category_id;

        $name = $request->name;

        $description = $request->description;

        $text1 = $request->text1;

        $text2 = $request->text2;

        $text3 = $request->text3;

        $status = $request->status;

        $position = $request->position;

        $home_display = $request->home_display;

        $commission_percentage = $request->commission_percentage;

        $video_link = $request->video_link;

        $image = $request->file('image');

        $ratecard = $request->file('ratecard');

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'text1' => 'required',
            'text2' => 'required',
            'text3' => 'required',
            'status' => 'required',
            'commission_percentage' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        if($id>0) {
            $exists = DB::table('em_sub_category')->where('name', $name)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_sub_category')->where('name', $name)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Sub Category Already Exists'], 201);
        }  

        if($id>0) {
            $category = SubCategory::find($id);
        }   else {
            $category = new SubCategory();
        }

        if (!empty($image)) {

            $categoryImage = 'category-' .rand().time() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/categories');

            $image->move($destinationPath, $categoryImage);

            $category->image =  $categoryImage;

        }

        if (!empty($ratecard)) {

            $categoryImage = 'category-' .rand().time() . '.' . $ratecard->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/categories');

            $ratecard->move($destinationPath, $categoryImage);

            $category->ratecard =  $categoryImage;

        }

        $input = $request->all();
        //echo "<pre>"; print_r($input); exit;

        $category->category_id = $category_id;

        $category->name = $name;

        $category->description = $description;

        $category->text1 = $text1;

        $category->text2 = $text2;

        $category->text3 = $text3;

        $category->status = $status;

        $category->position = $position;

        $category->home_display = $home_display;

        $category->commission_percentage = $commission_percentage;

        if(!empty($video_link)) {
            $video_link = "https://www.youtube.com/embed/".$video_link."?&autoplay=1";
        }

        $category->video_link = $video_link;

        $category->save();

        $name_lang = $request->name_lang;

        $description_lang = $request->description_lang;

        $text1_lang = $request->text1_lang;

        $text2_lang = $request->text2_lang;

        $text3_lang = $request->text3_lang;

        if(isset($name_lang) && count($name_lang)>0) {
            foreach ($name_lang as $language_id => $value) {
                $exists_lang = DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$category->id,
                             'level'=>2])->first();

                if(!empty($exists_lang)) {
                    DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$category->id,
                             'level'=>2])
                    ->update([
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'text1' => $text1_lang[$language_id],
                        'text2' => $text2_lang[$language_id],
                        'text3' => $text3_lang[$language_id],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }   else {
                    DB::table('em_category_language')->insert([
                        'language_id'=>$language_id,
                        'category_id'=>$category->id,
                        'level'=>2,
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'text1' => $text1_lang[$language_id],
                        'text2' => $text2_lang[$language_id],
                        'text3' => $text3_lang[$language_id],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }


        return response()->json(['status' => 'SUCCESS', 'message' => 'Sub Category has been Saved'], 201);

    }    

    public function editSubCategory($id)
    {   
        if(Auth::check()){
        $category = Category::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();

        $subcategory = SubCategory::where('id', $id)->first();

        $languages = [];
        $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
        $language_content = DB::table('em_category_language')
                    ->where(['category_id'=>$id, 'level'=>2])->get();  
        $lang_arr = [];
        if(count($language_content)>0) {
            foreach ($language_content as $key => $value) {
                $lang_arr[$value->language_id]['title'] = $value->title;
                $lang_arr[$value->language_id]['description'] = $value->description;
                $lang_arr[$value->language_id]['text1'] = $value->text1;
                $lang_arr[$value->language_id]['text2'] = $value->text2;
                $lang_arr[$value->language_id]['text3'] = $value->text3;
            }
        }

        return view('admin.sub_categories_add')->with('subcategory', $subcategory)->with('category', $category)
            ->with('languages', $languages)->with('language_content', $lang_arr);
        }else{
            return redirect('/admin/login');
        }
    }

    // Sub Category - Additional Instructions
    public function subcategoryInstructions($id)
    {
        if(Auth::check()){  
            $services = SubCategory::where('id', $id)->first();
            return view('admin.service_instructions')->with('services', $services);
        }else{
            return redirect('/admin/login');
        }
    }

    public function getSubcategoryInstructions(Request $request){
        $sub_category_id = $request->id;
        $instructions = ServiceInstructions::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_subcategory_instructions.sub_category_id')
            ->where('em_subcategory_instructions.sub_category_id', $sub_category_id)
            ->select('em_subcategory_instructions.*', 'em_sub_category.name as sub_category_name')
            ->get();

        return Datatables::of($instructions)->make(true);
    }

    public function editSubcategoryInstruction(Request $request) {
        $insid = $request->code;

        $service_instruction = ServiceInstructions::where('id', $insid)->first();

        return response()->json(['status' => 'SUCCESS', 'data' => $service_instruction], 201);
    }

    public function addSubcategoryInstruction(Request $request) {  

        $sub_category_id = $request->service_id;
        $instruction_type = $request->instruction_type;
        $instruction = $request->instruction;
        $display = $request->display;

        $ins = DB::table('em_subcategory_instructions')->where('instruction',$instruction)
            ->where('instruction_type',$instruction_type)
            ->where('sub_category_id',$sub_category_id)
            ->first();

        if(empty($ins)){

            DB::table('em_subcategory_instructions')
                ->insert(
                    [
                        'sub_category_id'=>$sub_category_id,
                        'instruction_type'=>$instruction_type,
                        'instruction'=>$instruction,
                        'status'=>$display,
                        'created_at'=>date('Y-m-d H:i:s')
                    ]
                );

                /*$service_ids = $request->add_service_ids;
                if(!empty($service_ids) && count($service_ids)>0) {
                    foreach ($service_ids as $key => $value) {
                        $ins = DB::table('em_subcategory_instructions')->where('instruction',$instruction)
                            ->where('instruction_type',$instruction_type)
                            ->where('sub_category_id',$value)
                            ->first();

                        if(empty($ins)){

                            DB::table('em_subcategory_instructions')
                                ->insert(
                                    [
                                        'service_id'=>$value,
                                        'instruction_type'=>$instruction_type,
                                        'instruction'=>$instruction,
                                        'status'=>$display,
                                        'created_at'=>date('Y-m-d H:i:s')
                                    ]
                                );
                        }
                    }
                }*/

            return response()->json(['status' => 'SUCCESS', 'message' => 'The Instruction has added'], 201);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'The Instruction Exists'], 201);
        }
    }

    public function updateSubcategoryInstruction(Request $request) {  
        $instruction_id = $request->instruction_id; 
        $sub_category_id = $request->edit_service_id;
        $instruction_type = $request->edit_instruction_type;
        $instruction = $request->edit_instruction;
        $display = $request->display;

        $ins = DB::table('em_subcategory_instructions')->where('instruction',$instruction)
            ->where('instruction_type',$instruction_type)
            ->where('sub_category_id',$sub_category_id)
            ->whereNotIN('id',[$instruction_id])
            ->first();

        if(empty($ins)){

            DB::table('em_subcategory_instructions')
                ->where('id', $instruction_id)
                ->update(
                    [
                        'sub_category_id'=>$sub_category_id,
                        'instruction_type'=>$instruction_type,
                        'instruction'=>$instruction,
                        'status'=>$display,
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]
                );

            return response()->json(['status' => 'SUCCESS', 'message' => 'The Instruction has saved'], 201);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'The Instruction Exists'], 201);
        }
    }


    // Request Other Services

    public function viewOtherServices(){
        if(Auth::check()){
        return view('admin.otherservices');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getOtherServices(){

        $services = DB::table('em_servicer_otherservices')
            ->leftjoin('users', 'users.id', '=', 'em_servicer_otherservices.service_provider_id')
            ->where('em_servicer_otherservices.status', 'PENDING')
            ->select('em_servicer_otherservices.*', 'users.name as user_name', 'users.mobile', 'users.reg_no')
            ->get();

        return Datatables::of($services)->make(true);
    }

    public function approveOtherService(Request $request)
    {

        $id = $request->id; 

        if($id > 0) {

            $other = DB::table('em_servicer_otherservices')->where('id', $id)->first();
            if(!empty($other)) {

                $service_provider_id = $other->service_provider_id;
                $exuser = ServiceProvider::where('user_id', $service_provider_id)->first(); 

                if(empty($exuser)) {
                    return response()->json([ 'status' => "FAILED", 'message' => 'Invalid Service Provider']);
                }

                $exists = DB::table('em_sub_category')->where('name', $other->service_name)->first();
                if(!empty($exists)) {
                    return response()->json(['status' => 'FAILED', 'message' => 'Service Name Already Exists'], 201);
                }  

                $service = new SubCategory();

                $service->category_id = 1;
                $service->image = 'home.jpeg';
                $service->name = $other->service_name;
                $service->description = $other->service_description;
                $service->status = 'ACTIVE';
                $service->created_at = date('Y-m-d H:i:s');

                $service->save();

                $service_id = $service->id;

                $service->position = $service_id;
                
                $service->save();

                $sub_category_ids = $exuser->sub_category_ids;
                if(!empty($sub_category_ids)) {
                    $exuser->sub_category_ids = $sub_category_ids.','.$service_id;
                }   else {
                    $exuser->sub_category_ids = $service_id;
                }
                
                $exuser->save();

                DB::table('em_servicer_otherservices')->where('id', $id)
                    ->update([
                        'status' => 'APPROVED',
                        'approved_by' => Auth::User()->id,
                        'approved_on' => date('Y-m-d H:i:s')
                    ]);

                return response()->json([

                    'status' => "SUCCESS",
                    'message' => "Service Approved Successfully",
                ]);
            }   else {
                return response()->json([

                    'status' => "FAILED",
                    'message' => "Invalid inputs",
                ]);
            }
            
        }
        return response()->json([

            'status' => "FAILED",
            'message' => "Invalid inputs",
        ]);

    }

    public function rejectOtherService(Request $request)
    {

        $id = $request->id; 

        if($id > 0) {

            $other = DB::table('em_servicer_otherservices')->where('id', $id)->first();
            if(!empty($other)) {

                $service_provider_id = $other->service_provider_id;
                $exuser = ServiceProvider::where('user_id', $service_provider_id)->first(); 

                if(empty($exuser)) {
                    return response()->json([ 'status' => "FAILED", 'message' => 'Invalid Service Provider']);
                }

                DB::table('em_servicer_otherservices')->where('id', $id)
                    ->update([
                        'status' => 'REJECTED',
                        'updated_by' => Auth::User()->id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                $fcmMsg = array("fcm" => array("notification" => array(
                    "title" => "Service ". $other->service_name." Rejected",
                    "body" => "Service ". $other->service_name." Rejected By Admin in ". config("constants.site_name"),
                    "type" => "9",
                  )));

                CommonController::push_notification($other->service_provider_id, $fcmMsg);

                return response()->json([

                    'status' => "SUCCESS",
                    'message' => "Service Rejected Successfully",
                ]);
            }   else {
                return response()->json([

                    'status' => "FAILED",
                    'message' => "Invalid inputs",
                ]);
            }
            
        }
        return response()->json([

            'status' => "FAILED",
            'message' => "Invalid inputs",
        ]);

    }

    // Services

    public function viewServices(){
        if(Auth::check()){
        return view('admin.services');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getServices(Request $request){

        /*$services = Services::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
            ->select('em_sub_cat_services.*', 'em_category.name as category_name', 'em_sub_category.name as sub_category_name')
            ->get();

        return Datatables::of($services)->make(true); */

        $input = $request->all();
        $start = $input['start'];
        $length = $input['length'];

        $input = $request->all(); 
        $columns = $request->get('columns'); 
    
        $servicesqry = Services::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
            ->select('em_sub_cat_services.*', 'em_category.name as category_name', 'em_sub_category.name as sub_category_name');

        $filteredqry = Services::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
            ->select('em_sub_cat_services.*', 'em_category.name as category_name', 'em_sub_category.name as sub_category_name');
        
        if (count($columns) > 0) {
            foreach ($columns as $key => $value) {
                if (! empty($value['name']) && ! empty($value['search']['value'])) {
                    $servicesqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    $filteredqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                }
            }
        }
        $services = $servicesqry->skip($start)->take($length)->get();
        $filters = $filteredqry->select('em_sub_cat_services.id')->count();  

        $totalData = Services::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
            ->select('em_sub_cat_services.id')->count();  

        $totalFiltered = $totalData;   
        if(!empty($filters)) {
            $totalFiltered = $filters;
        }  
        
        $data = [];
        if(!empty($services))    {
            foreach ($services as $post)
            {  
                $data[] = $post;
            }
        }
        

        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data);  
    }

    public function getServicesExcel(Request $request)    { 
        if (Auth::check()) {
            
            $input = $request->all();
            $start = $input['start'];
            $length = $input['length'];

            $input = $request->all(); 
            $columns = $request->get('columns'); 
        
            $categoriesqry = Services::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
                ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                ->select('em_sub_cat_services.*', 'em_category.name as category_name', 'em_sub_category.name as sub_category_name');
            
            if (count($columns) > 0) {
                foreach ($columns as $key => $value) {
                    if (! empty($value['name']) && ! empty($value['search']['value'])) {
                        $categoriesqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    }
                }
            }
            $categories = $categoriesqry->get();
                   
            $categories_excel = [];
            if (! empty($categories)) {
                foreach ($categories as $rev) {
                    
                    $created_date = $rev->created_at;
                    $my_date = strtotime($created_date);
                    $created_date = date("Y-m-d h:i:a", $my_date);

                    $serbase = ' Fixed Price';
                    $service_based_on = $rev->service_based_on;
                    if($service_based_on == 1) $serbase =  ' Hourly Based';
                    else if($service_based_on == 2) $serbase =  ' Fixed Price';

                    $categories_excel[] = [
                        "Category" => $rev->category_name,
                        "Sub Category" => $rev->sub_category_name,
                        "Name" => $rev->name,
                        "Service Based On" => $serbase,
                        "Display Text" => $rev->display_text,
                        "Position" => $rev->position,
                        "Status" => $rev->status,
                    ];
                }
            }

            header("Content-Type: text/plain");
            $flag = false;
            foreach ($categories_excel as $row) {
                if (! $flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            exit();
          
        } else {
            return redirect('/admin/login');
        }
    }

    public function addNewService(){
        if(Auth::check()){
        $subcategory = SubCategory::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();
        $languages = [];
        $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
        return view('admin.services_add')->with('subcategory', $subcategory)->with('languages', $languages);
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveService(Request $request)
    {

        $id = $request->id;

        $sub_category_id = $request->sub_category_id;

        $name = $request->name;

        $description = $request->description;

        $status = $request->status;

        $service_based_on = $request->service_based_on;

        $display_text = $request->display_text;

        $position = $request->position;

        $image = $request->file('image');

        $validator = Validator::make($request->all(), [
            'sub_category_id' => 'required',
            'name' => 'required',
            /*'description' => 'required',
            'display_text' => 'required',*/
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        /*if($id>0) {
            $exists = DB::table('em_sub_cat_services')->where('name', $name)->where('sub_category_id', $sub_category_id)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_sub_cat_services')->where('name', $name)->where('sub_category_id', $sub_category_id)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Service Already Exists'], 201);
        }*/  

        if($id>0) {
            $service = Services::find($id);
        }   else {
            $service = new Services;
        }

        if (!empty($image)) {

            $categoryImage = 'services-' .rand().time() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/services');

            $image->move($destinationPath, $categoryImage);

            $service->image = $categoryImage;

        }

        $service->sub_category_id = $sub_category_id;

        $service->name = $name;

        $service->description = $description;

        $service->service_based_on = $service_based_on;

        $service->display_text = $display_text;

        $service->position = $position;

        $service->status = $status;

        $service->save();

        $name_lang = $request->name_lang;

        $description_lang = $request->description_lang;

        $display_text_lang = $request->display_text_lang;

        if(isset($name_lang) && count($name_lang)>0) {
            foreach ($name_lang as $language_id => $value) {
                $exists_lang = DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$service->id,
                             'level'=>3])->first();

                if(!empty($exists_lang)) {
                    DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$service->id,
                             'level'=>3])
                    ->update([
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'text1' => $display_text_lang[$language_id],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }   else {
                    DB::table('em_category_language')->insert([
                        'language_id'=>$language_id,
                        'category_id'=>$service->id,
                        'level'=>3,
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'text1' => $display_text_lang[$language_id],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }


        return response()->json(['status' => 'SUCCESS', 'message' => 'Service has been Saved'], 201);

    }    

    public function editService($id)
    {
        if(Auth::check()){
        $subcategory = SubCategory::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();

        $service = Services::where('id', $id)->first();

        $languages = [];
        $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
        $language_content = DB::table('em_category_language')
                    ->where(['category_id'=>$id, 'level'=>3])->get();  
        $lang_arr = [];
        if(count($language_content)>0) {
            foreach ($language_content as $key => $value) {
                $lang_arr[$value->language_id]['title'] = $value->title;
                $lang_arr[$value->language_id]['description'] = $value->description;
                $lang_arr[$value->language_id]['text1'] = $value->text1;
            }
        }

        return view('admin.services_add')->with('service', $service)->with('subcategory', $subcategory)
            ->with('languages', $languages)->with('language_content', $lang_arr);
        }else{
            return redirect('/admin/login');
        }
    }

    // Sub Services

    public function loadServices(Request $request) {
        $subcatid = $request->subcatid;
        $service = Services::where('sub_category_id', $subcatid)->get();
        $serviceoption = '<option value="">Select Services</option>';
        if(count($service)>0) {
            foreach ($service as $key => $value) {
                $serviceoption .= '<option value="'.$value->id.'">'.$value->name.'</option>';
            }
        }
        return response()->json(['status' => 'SUCCESS', 'message' => 'Service List', 'data'=>$serviceoption]);
    }

    public function viewSubServices(){
        if(Auth::check()){
        return view('admin.sub_services');
        }else{
            return redirect('/admin/login');
        }
    }

    /*public function getSubServices(){

        $services = SubServices::leftjoin('em_sub_cat_services', 'em_sub_cat_services.id', '=', 'em_sub_service.service_id')
            ->leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->select('em_sub_service.*', 'em_sub_cat_services.name as service_name', 'em_sub_category.name as sub_category_name')
            ->get();

        return Datatables::of($services)->make(true);
    }*/

    public function getSubServices(Request $request){
 
        $input = $request->all();
        $start = $input['start'];
        $length = $input['length'];

        $input = $request->all(); 
        $columns = $request->get('columns'); 
    
        $servicesqry = SubServices::leftjoin('em_sub_cat_services', 'em_sub_cat_services.id', '=', 'em_sub_service.service_id')
            ->leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->select('em_sub_service.*', 'em_sub_cat_services.name as service_name', 'em_sub_category.name as sub_category_name');

        $filteredqry = SubServices::leftjoin('em_sub_cat_services', 'em_sub_cat_services.id', '=', 'em_sub_service.service_id')
            ->leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->select('em_sub_service.*', 'em_sub_cat_services.name as service_name', 'em_sub_category.name as sub_category_name');
        
        if (count($columns) > 0) {
            foreach ($columns as $key => $value) {
                if (! empty($value['name']) && ! empty($value['search']['value'])) {
                    $servicesqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    $filteredqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                }
            }
        }
        $services = $servicesqry->skip($start)->take($length)->get();
        $filters = $filteredqry->select('em_sub_service.id')->count(); 
        
        $totalData = SubServices::leftjoin('em_sub_cat_services', 'em_sub_cat_services.id', '=', 'em_sub_service.service_id')
            ->leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
            ->select('em_sub_service.id')->count();  

        $totalFiltered = $totalData;   
        if(!empty($filters)) {
            $totalFiltered = $filters;
        }  
        
        $data = [];
        if(!empty($services))    {
            foreach ($services as $post)
            {  
                $data[] = $post;
            }
        }
        

        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data);  
    }

    public function getSubServicesExcel(Request $request)    { 
        if (Auth::check()) {
            
            $input = $request->all();
            $start = $input['start'];
            $length = $input['length'];

            $input = $request->all(); 
            $columns = $request->get('columns'); 
        
            $categoriesqry = SubServices::leftjoin('em_sub_cat_services', 'em_sub_cat_services.id', '=', 'em_sub_service.service_id')
                ->leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
                ->select('em_sub_service.*', 'em_sub_cat_services.name as service_name', 'em_sub_category.name as sub_category_name');
            
            if (count($columns) > 0) {
                foreach ($columns as $key => $value) {
                    if (! empty($value['name']) && ! empty($value['search']['value'])) {
                        $categoriesqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    }
                }
            }
            $categories = $categoriesqry->get();
                   
            $categories_excel = [];
            if (! empty($categories)) {
                foreach ($categories as $rev) {
                    
                    $created_date = $rev->created_at;
                    $my_date = strtotime($created_date);
                    $created_date = date("Y-m-d h:i:a", $my_date);

                    $serbase = ' Fixed Price';
                    $price_based_on = $rev->price_based_on;
                    if($price_based_on == 1) $serbase =  ' Per Hour Price';
                    else if($price_based_on == 2) $serbase =  ' Fixed Price';
     
                    $categories_excel[] = [
                        "Sub Category" => $rev->sub_category_name,
                        "Service Name" => $rev->service_name,
                        "Name" => $rev->name,
                        "Price Based On" => $price_based_on,
                        "Price" => $rev->price,
                        "Offer Price" => $rev->offer_price,
                        "Position" => $rev->position,
                        "Status" => $rev->status,
                    ];
                }
            }

            header("Content-Type: text/plain");
            $flag = false;
            foreach ($categories_excel as $row) {
                if (! $flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            exit();
          
        } else {
            return redirect('/admin/login');
        }
    }

    public function addNewSubService(){
        if(Auth::check()){
            $subcategory = SubCategory::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();
            $services = Services::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();
            $languages = [];
            $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
            return view('admin.sub_services_add')->with('services', $services)->with('subcategory', $subcategory)->with('languages', $languages);
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveSubService(Request $request)
    {

        $id = $request->id;

        $service_id = $request->service_id;

        $name = $request->name;

        $description = $request->description;

        $status = $request->status;

        $position = $request->position;

        $image = $request->file('image');

        $price_based_on = $request->price_based_on;

        $price = $request->price;

        $offer_price = $request->offer_price;

        $validator = Validator::make($request->all(), [
            'service_id' => 'required',
            'name' => 'required',
            'price' => 'required',
            'offer_price' => 'required',
            /*'description' => 'required',*/
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        /*if($id>0) {
            $exists = DB::table('em_sub_service')->where('name', $name)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_sub_service')->where('name', $name)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Sub Service Already Exists'], 201);
        }  */

        if($id>0) {
            $service = SubServices::find($id);
        }   else {
            $service = new SubServices;
        }

        if (!empty($image)) {

            $categoryImage = 'services-' .rand().time() . '.' . $image->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/services');

            $image->move($destinationPath, $categoryImage);

            $service->image = 'uploads/services/' . $categoryImage;

        }

        $service->service_id = $service_id;

        $service->name = $name;

        $service->description = $description;

        $service->status = $status;

        $service->price_based_on = $price_based_on;

        $service->price = $price;

        $service->offer_price = $offer_price;

        $service->position = $position; 

        $service->save();

        $name_lang = $request->name_lang;

        $description_lang = $request->description_lang;

        if(isset($name_lang) && count($name_lang)>0) {
            foreach ($name_lang as $language_id => $value) {
                $exists_lang = DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$service->id,
                             'level'=>4])->first();
                    
                if(!empty($exists_lang)) {
                    DB::table('em_category_language')
                    ->where(['language_id'=>$language_id,
                             'category_id'=>$service->id,
                             'level'=>4])
                    ->update([
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }   else {
                    DB::table('em_category_language')->insert([
                        'language_id'=>$language_id,
                        'category_id'=>$service->id,
                        'level'=>4,
                        'title' => $name_lang[$language_id],
                        'description' => $description_lang[$language_id],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }


        return response()->json(['status' => 'SUCCESS', 'message' => 'Sub Service has been Saved'], 201);

    }    

    public function editSubService($id)
    {
        if(Auth::check()){
            $subcategory = SubCategory::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();

            $services = Services::where('status', 'ACTIVE')->orderby('name', 'ASC')->get();

            $subservice = SubServices::where('id', $id)->first();

            if(!empty($subservice)) {
                $service_id = $subservice->service_id;
                $sub_category_id = Services::where('id', $service_id)->value('sub_category_id');
            }

            $languages = [];
            $languages = Languages::where('status', 'ACTIVE')->where('by_default', 0)->get(); 
            $language_content = DB::table('em_category_language')
                        ->where(['category_id'=>$id, 'level'=>4])->get();  
            $lang_arr = [];
            if(count($language_content)>0) {
                foreach ($language_content as $key => $value) {
                    $lang_arr[$value->language_id]['title'] = $value->title;
                    $lang_arr[$value->language_id]['description'] = $value->description;
                }
            }

            return view('admin.sub_services_add')->with('services', $services)->with('subservice', $subservice)
                ->with('subcategory', $subcategory)->with('sel_sub_category_id', $sub_category_id)
                ->with('sel_service_id', $service_id)->with('languages', $languages)
                ->with('language_content', $lang_arr);
        }else{
            return redirect('/admin/login');
        }
    }

    // Days

    public function viewDays(){
        if(Auth::check()){
        return view('admin.days');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getDays(){

        $days = Days::all();

        return Datatables::of($days)->make(true);
    }

    public function saveDay(Request $request)
    {

        $id = $request->id;

        $day = $request->day;

        $status = $request->status;

        $validator = Validator::make($request->all(), [
            'day' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        if($id>0) {
            $exists = DB::table('em_day_list')->where('day', $day)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_day_list')->where('day', $day)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Day Already Exists'], 201);
        }  

        if($id>0) {
            $days = days::find($id);
        }   else {
            $days = new Days;
        }

        $days->day = $day;

        $days->status = $status;

        $days->save();


        return response()->json(['status' => 'SUCCESS', 'message' => 'Day has been Saved'], 201);

    }    

    public function editDay(Request $request)
    {
        if(Auth::check()){
            $day = Days::where('id', $request->code)->get();
            if($day->isNotEmpty()) {
                return response()->json(['status' => 'SUCCESS', 'data' => $day[0], 'message' => 'Day Detail']);
            }   else {
                return response()->json(['status' => 'FAILED', 'data' => [], 'message' => 'No Day Detail']);
            }
        }else{
            return redirect('/admin/login');
        }
    }


    // Cancellation Policies

    public function viewCancellationPolicies(){
        if(Auth::check()){
        return view('admin.cancellation_policies');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getCancellationPolicies(){

        $cancellation_policies = CancellationPolicies::all();

        return Datatables::of($cancellation_policies)->make(true);
    }

    public function addNewCancellationPolicy(){
        if(Auth::check()){
        return view('admin.cancellation_policy_add');
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveCancellationPolicy(Request $request)
    {

        $id = $request->id;

        $policy_type = $request->policy_type;

        $policy_description = $request->policy_description;

        $policy_hours = $request->policy_hours;

        $is_refund_avail = $request->is_refund_avail;

        $refund_amount = $request->refund_amount;

        $status = $request->status;

        $validator = Validator::make($request->all(), [
            'policy_type' => 'required',
            'policy_description' => 'required',
            'policy_hours' => 'required',
            'is_refund_avail' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        if($id>0) {
            $exists = DB::table('em_cancellation_policies')->where('policy_type', $policy_type)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_cancellation_policies')->where('policy_type', $policy_type)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Cancellation Policy Already Exists'], 201);
        }  

        if($id>0) {
            $cancellation_policy = CancellationPolicies::find($id);
        }   else {
            $cancellation_policy = new CancellationPolicies;
        }

        $cancellation_policy->policy_type = $policy_type;

        $cancellation_policy->policy_description = $policy_description;

        $cancellation_policy->policy_hours = $policy_hours;

        $cancellation_policy->is_refund_avail = $is_refund_avail;  

        $cancellation_policy->refund_amount = $refund_amount;  

        $cancellation_policy->status = $status;

        $cancellation_policy->save();

        return response()->json(['status' => 'SUCCESS', 'message' => 'Cancellation Policy has been Saved'], 201);

    }    

    public function editCancellationPolicy($id)
    {
        if(Auth::check()){
            $cancellation_policy = CancellationPolicies::where('id', $id)->first();

            return view('admin.cancellation_policy_add')->with('cancellation_policy', $cancellation_policy);
        }else{
            return redirect('/admin/login');
        }
    }

    // Job Status

    public function viewJobStatus(){
        if(Auth::check()){
        return view('admin.job_status');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getJobStatus(){

        $job_status = JobStatus::all();

        return Datatables::of($job_status)->make(true);
    }

    public function addNewJobStatus(){
        if(Auth::check()){
        return view('admin.job_status_add');
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveJobStatus(Request $request)
    {

        $id = $request->id;

        $status_value = $request->status_value;

        $status_description = $request->status_description;

        $status = $request->status;

        $validator = Validator::make($request->all(), [
            'status_value' => 'required',
            'status_description' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        if($id>0) {
            $exists = DB::table('em_job_status')->where('status_description', $status_description)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_job_status')->where('status_description', $status_description)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Job Status Already Exists'], 201);
        }  

        if($id>0) {
            $job_status = JobStatus::find($id);
        }   else {
            $job_status = new JobStatus;
        }

        $job_status->status_value = $status_value;

        $job_status->status_description = $status_description;

        $job_status->status = $status;

        $job_status->save();

        return response()->json(['status' => 'SUCCESS', 'message' => 'Job Status has been Saved'], 201);

    }    

    public function editJobStatus($id)
    {
        if(Auth::check()){
            $job_status = JobStatus::where('id', $id)->first();

            return view('admin.job_status_add')->with('job_status', $job_status);
        }else{
            return redirect('/admin/login');
        }
    }

    // Fees Types

    public function viewFeesTypes(){
        if(Auth::check()){
        return view('admin.fees_types');
        }else{
            return redirect('/admin/login');
        }
    }

    public function getFeesTypes(){

        $fees_types = FeesTypes::all();

        return Datatables::of($fees_types)->make(true);
    }

    public function addNewFeesTypes(){
        if(Auth::check()){
        return view('admin.fees_types_add');
        }else{
            return redirect('/admin/login');
        }
    }

    public function saveFeesTypes(Request $request)
    {

        $id = $request->id;

        $fees_type = $request->fees_type;

        $status = $request->status;

        $validator = Validator::make($request->all(), [
            'fees_type' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Please check your all inputs",
            ]);
        }

        if($id>0) {
            $exists = DB::table('em_fees_types')->where('fees_type', $fees_type)->whereNotIn('id', [$id])->first();
        }   else {
            $exists = DB::table('em_fees_types')->where('fees_type', $fees_type)->first();
        }

        if(!empty($exists)) {
            return response()->json(['status' => 'FAILED', 'message' => 'Fees Types Already Exists'], 201);
        }  

        if($id>0) {
            $fees_types = FeesTypes::find($id);
        }   else {
            $fees_types = new FeesTypes;
        }

        $fees_types->fees_type = $fees_type;

        $fees_types->status = $status;

        $fees_types->save();

        return response()->json(['status' => 'SUCCESS', 'message' => 'Fees Types has been Saved'], 201);

    }    

    public function editFeesTypes($id)
    {
        if(Auth::check()){
            $fees_types = FeesTypes::where('id', $id)->first();

            return view('admin.fees_types_add')->with('fees_types', $fees_types);
        }else{
            return redirect('/admin/login');
        }
    }

    // Role Users
    /*
     * Function: viewRoleUsers
     */
    public function viewRoleUsers()
    {
        if (Auth::check()) {
            $roles = UserRoles::where('status', 'ACTIVE')->get();
            return view('admin.roleusers')->with('roles', $roles);
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: getRoleUsers
     * Datatable Load
     */
    public function getRoleUsers(Request $request)
    {
        if (Auth::check()) {

            $limit = $request->get('length', '10');
            $start = $request->get('start', '0');
            $dir = $request->input('order.0.dir');
            $columns = $request->get('columns');
            $order = $request->input('order.0.column');

            $users_qry = User::leftjoin('em_userroles', 'em_userroles.ref_code', 'users.user_type')
                ->whereNotIn('users.user_type', ['USER', 'SUPERADMIN', 'SERVICEPROVIDER', 'GUESTUSER', 'VENDOR', 'WAREHOUSE', 'DELIVERY_BOY']);

            //                ->select('users.*', 'sc_class_exam.class_exam')->orderby('users.id', 'desc')->get();
            //return Datatables::of($users)->make(true);

            if(count($columns)>0) { 
                foreach ($columns as $key => $value) { 
                    if(!empty($value['search']['value']) && !empty($value['name'])) {
                        $users_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                    }
                }
            }
            if(!empty($order)) {
                $orderby = $columns[$order]['name'];
            }   else {
                $orderby = 'users.id';
            }
            if(empty($dir)) {
                $dir = 'DESC';
            }
            

            $totalData = $users_qry->select('users.id')->get();
 
            if(!empty($totalData)) {
                $totalData = count($totalData);
            }
        
            $users = $users_qry->select('users.*', 'em_userroles.user_role')->orderBy($orderby,$dir)->offset($start)->limit($limit)->get();
            foreach ($users as $key => $value) {
                $created_date = $value->created_at;
                $my_date = strtotime($created_date);
                $created_date = date("Y-m-d h:i:a", $my_date);
                $users[$key]->created_date = $created_date;
            }

            $data = [];
            if(!empty($users))    {
                $users = $users->toArray();
                foreach ($users as $post)
                {   
                    $nestedData = [];
                    foreach($post as $k=>$v) { 
                        $nestedData[$k] = $v;
                    }
                    $data[] = $nestedData;
                }
            }
       // echo "<pre>"; print_r($data); exit;

            $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),   
                    "data"            => $data, 
                    "recordsFiltered" => intval($totalData),   
                    );
            
            echo json_encode($json_data); 

        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: postRoleUsers
     * Save into users table
     */
    public function postRoleUsers(Request $request)
    {
        if (Auth::check()) {
            $id = $request->id;
            $userrole = $request->userrole;
            $name = $request->name;
            $email = $request->email;
            $mobile = $request->mobile;
            $password = $request->password;
            $status = $request->status;

            $validator = Validator::make($request->all(), [
                'userrole' => 'required',
                'name' => 'required',
                'email' => 'required',
                'mobile' => 'required', 
                'status' => 'required'
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            if ($id > 0) {
                $user = User::find($id);
                $user->updated_at = date('Y-m-d H:i:s');
            } else {
                if(empty($password)) {
                    return response()->json([
                        'status' => "FAILED",
                        'message' => "Please Enter the Password"
                    ]);
                }
                $user = new User();
                $user->reg_no = rand() . time();
                $user->created_at = date('Y-m-d H:i:s');
            }
            if(!empty($password)) {
                $password = Hash::make($password);
                $user->password = $password;
            }

            $user->user_type = $userrole;
            $user->name = $name;
            $user->email = $email;
            $user->mobile = $mobile;
            $user->status = $status;
 
            $user->save();
            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'User Saved Successfully'
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function editRoleUsers(Request $request)
    {
        if (Auth::check()) {
            $user = User::where('id', $request->code)->get();
            if ($user->isNotEmpty()) {
                return response()->json([
                    'status' => 'SUCCESS',
                    'data' => $user[0],
                    'message' => 'User Detail'
                ]);
            } else {
                return response()->json([
                    'status' => 'FAILED',
                    'data' => [],
                    'message' => 'No User Detail'
                ]);
            }
        } else {
            return redirect('/admin/login');
        }
    }

    // Users
    /*
     * Function: viewUsers
     */
    public function viewUsers()
    {
        if (Auth::check()) {
            $countries = Countries::where('status', 'ACTIVE')->orderby('position', 'asc')->get();
            return view('admin.users')->with('countries', $countries);
        } else {
            return redirect('/admin/login');
        }
    }

    // Users
    public function userPage()
    {
        if(Auth::check()){
            $countries = Countries::where('status', 'ACTIVE')->orderby('position', 'asc')->get();
        return view('admin.users')->with('countries', $countries);
        }else{
            return redirect('/admin/login');
        }
    }

    /*public function getUsers(Request $request)
    {
        // ->where('step', '>', 0)
        $users = User::where('user_type', 'USER')->orderby('id', 'desc')->get(); 

        return Datatables::of($users)->make(true);

    }*/

    public function getUsers(Request $request){
 
        $input = $request->all();
        $start = $input['start'];
        $length = $input['length'];

        $input = $request->all(); 
        $columns = $request->get('columns'); 
    
        $usersqry = User::where('user_type', 'USER')->orderby('id', 'desc');
        $filteredqry = User::where('user_type', 'USER')->orderby('id', 'desc');
        
        if (count($columns) > 0) {
            foreach ($columns as $key => $value) {
                if (! empty($value['name']) && ! empty($value['search']['value'])) {
                    $usersqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    $filteredqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                }
            }
        }
        $users = $usersqry->skip($start)->take($length)->get();
        $filters = $filteredqry->select('id')->count(); 

        $totalData = User::where('user_type', 'USER')->orderby('id', 'desc')->select('id')->count();  

        $totalFiltered = $totalData;   
        if(!empty($filters)) {
            $totalFiltered = $filters;
        }  
        
        $data = [];
        if(!empty($users))    {
            foreach ($users as $post)
            {  
                $data[] = $post;
            }
        }
        

        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data);  
    }

    public function getUsersExcel(Request $request)    { 
        if (Auth::check()) {
            
            $input = $request->all();
            $start = $input['start'];
            $length = $input['length'];

            $input = $request->all(); 
            $columns = $request->get('columns'); 
        
            $categoriesqry = User::where('user_type', 'USER')->orderby('id', 'desc');
            
            if (count($columns) > 0) {
                foreach ($columns as $key => $value) {
                    if (! empty($value['name']) && ! empty($value['search']['value'])) {
                        $categoriesqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                    }
                }
            }
            $categories = $categoriesqry->get();
                   
            $categories_excel = [];
            if (! empty($categories)) {
                foreach ($categories as $rev) {
                    
                    $created_date = $rev->created_at;
                    $my_date = strtotime($created_date);
                    $created_date = date("Y-m-d h:i:a", $my_date);
             
                    $categories_excel[] = [
                        "User Name" => $rev->name,
                        "Email" => $rev->email,
                        "Mobile" => $rev->mobile,
                        "Country" => $rev->country_code,
                        "Referral Code" => $rev->referal_code,
                        "Status" => $rev->status,
                    ];
                }
            }

            header("Content-Type: text/plain");
            $flag = false;
            foreach ($categories_excel as $row) {
                if (! $flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            exit();
          
        } else {
            return redirect('/admin/login');
        }
    }

     /*
     * Function: postUsers
     * Save into users table
     */
    public function postUsers(Request $request)
    {
        if (Auth::check()) {
            $id = $request->id;
            $name = $request->user_name;
            $country_id = $request->country_id;
            $email = $request->email;
            $mobile = $request->mobile; 
            $status = $request->status;

            $validator = Validator::make($request->all(), [
                'country_id' => 'required',
                'user_name' => 'required',
                'email' => 'required',
                'mobile' => 'required', 
                'status' => 'required'
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            $mobileEx = DB::table('users')->where('mobile', $mobile)->where('user_type', 'USER');
            if ($id > 0) {
                $mobileEx->where('id', '!=', $id);
            } 
            $mobileEx = $mobileEx->first();

            if(!empty($mobileEx)) {
                return response()->json([
                    'status' => "FAILED",
                    'message' => "Mobile Number already exists"
                ]);
            }

            if ($id > 0) {
                $user = User::find($id);
                $user->updated_at = date('Y-m-d H:i:s');
            } else {
                $user = new User();
                $today = date('ymd');
                $fircheck_qry = "SELECT reg_no FROM users WHERE reg_no LIKE '$today%' ORDER BY id DESC LIMIT 1";
                $fircheck = DB::select($fircheck_qry); 
                if(is_array($fircheck) && count($fircheck) > 0) {
                    $reg_no = $fircheck[0]->reg_no;
                    $user_reg_no = $reg_no + 1;
                }   else {
                    $user_reg_no = $today . '0001';
                } 
                $user->reg_no = $user_reg_no;
                $user->created_at = date('Y-m-d H:i:s');

                $date = date('Y-m-d H:i:s');

                $user->referral_code = User::random_strings(5);
                $user->joined_date = date('Y-m-d H:i:s');
                $user->user_source_from = 'ADMIN';
                $user->api_token = User::random_strings(30);
                $def_expiry_after =  CommonController::getDefExpiry();
                $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                $user->wallet_amount = 0;
                $user->gender = 'MALE';

            } 

            $country_code = DB::table('em_countries')->where('id', $country_id)->value('phonecode');
   
            $user->user_type = 'USER';
            $user->name = $name;
            $user->email = $email;
            $user->mobile = $mobile;
            $user->country = $country_id;
            $user->country_code = $country_code;
            $user->code_mobile = $country_code.$mobile;
            $user->status = $status;
            
            $user->save();

            dispatch(new UserUpdateEmailSender($user));
            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'User Saved Successfully'
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function editUser($id,$code){
        if(Auth::check()){
        $user = User::find($id);
        return view('admin.user_edit')->with('user',$user);
        }else{
            return redirect('/admin/login');
        }
    }

    public function putUser(Request $request){
        if(Auth::check()){
        $userid = $request->user_id;
        $status = $request->status;

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Invalid Inputs"

            ]);
        }

        $user = User::find($userid);
        $user->status = $status;
        $user->save();

        if(!empty($user)){

            return response()->json(['status' => 'SUCCESS', 'message' => 'User Status Updated']);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
        }
        }else{
            return redirect('/admin/login');
        }
    }

    // Provider Payouts
    public function viewPayouts()
    {
        if(Auth::check()){
            $status = 0;
            return view('admin.provider_payouts')->with('status', $status);
        }else{
            return redirect('/admin/login');
        }
    }

    public function getPayouts(Request $request)
    {
        $limit = $request->get('length', 10);
        $page_no = $request->get('start', 0);

        $mindate = $request->get('minDateFilter', '');
        $maxdate = $request->get('maxDateFilter', '');
       
        $columns = $request->get('columns', []);
        $dir = $request->input('order.0.dir');
        $orderby = $columns[$request->input('order.0.column')]['name'];

        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns', []);


        $status = $request->get('status', '0');

        $bookings_qry = Booking::select('em_booking.id', 'em_booking.user_id','em_booking.service_provider_id', 'ref_no', 'sub_total', 'em_booking.total_amount', 'users.mobile',
            'code', 'commission_percentage', 'commission_amount', 'em_service_provider_payments.total_amount as servicer_amount', 'transaction_details', 'transaction_amount',  'mode', 'em_service_provider_payment_details.payment_date as payout_date', 'em_service_provider_payment_details.comments') 
                ->leftjoin('em_service_provider_payments', 'em_service_provider_payments.booking_id', 'em_booking.id')
                ->leftjoin('em_service_provider_payment_details', 'em_service_provider_payment_details.booking_id', 'em_service_provider_payments.booking_id')
                ->leftjoin('users', 'users.id', 'em_booking.service_provider_id')
                ->where('em_service_provider_payments.provider_settlement', 'PAID');


        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
        
        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $bookings_qry->whereRaw('payment_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $bookings_qry->whereRaw('payment_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");

        $bookings =   $bookings_qry->groupBy('em_booking.id')->orderby($orderby,$ordermode)
            ->skip($page_no)->limit($limit)
            ->get();

        $totalData = DB::table('em_booking')->select('em_booking.id')
            ->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))")->get();

        if(!empty($totalData)) {
            $totalData = $totalData->toArray();
            $totalData = count($totalData);
        }
        $totalFiltered = $totalData; 

        $totalFilteredqry  = Booking::select('em_booking.id', 'ref_no', 'sub_total', 'em_booking.total_amount',
            'code', 'commission_percentage', 'commission_amount', 'em_service_provider_payments.total_amount as servicer_amount', 'transaction_details', 'transaction_amount',  'mode', 'em_service_provider_payment_details.payment_date as payout_date', 'em_service_provider_payment_details.comments', 'users.mobile') 
                ->leftjoin('em_service_provider_payments', 'em_service_provider_payments.booking_id', 'em_booking.id')
                ->leftjoin('em_service_provider_payment_details', 'em_service_provider_payment_details.booking_id', 'em_service_provider_payments.booking_id')
                ->leftjoin('users', 'users.id', 'em_booking.service_provider_id')
                ->where('em_service_provider_payments.provider_settlement', 'PAID');


        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $totalFilteredqry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
        
        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $totalFilteredqry->whereRaw('payment_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $totalFilteredqry->whereRaw('payment_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $totalFilteredqry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $totalFilteredqry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");

        //$bookings =   $totalFilteredqry->groupBy('em_booking.id')->orderby($orderby,$ordermode)->get();
        
        $totalFiltered  =   $totalFilteredqry->orderby($orderby,$ordermode)->count();
 
        $data = [];
        if(!empty($bookings))    { 
            $datum = $bookings->toArray(); 
            foreach ($datum as $k => $post)
            {  

                foreach ($post as $k1 => $v1) {
                    $nestedData[$k1] = $v1;
                } 
                $data[] = $nestedData;
            }
        }
    

        $json_data = array(
                "draw"            => intval($request->input('draw')),  
                "recordsTotal"    => intval($totalData),  
                "recordsFiltered" => intval($totalFiltered), 
                "data"            => $data   
                );
        
        echo json_encode($json_data); 

    }

    public function getPayoutsExcel(Request $request)
    {
        $limit = $request->get('length', 10);
        $page_no = $request->get('start', 0);

        $mindate = $request->get('minDateFilter', '');
        $maxdate = $request->get('maxDateFilter', '');
       
        $columns = $request->get('columns', []);
        $dir = $request->input('order.0.dir');
        $orderby = $columns[$request->input('order.0.column')]['name'];

        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns', []);


        $status = $request->get('status', '0');

        $bookings_qry = Booking::select('em_booking.id', 'em_booking.user_id','em_booking.service_provider_id', 'ref_no', 'sub_total', 'em_booking.total_amount', 'users.mobile',
            'code', 'commission_percentage', 'commission_amount', 'em_service_provider_payments.total_amount as servicer_amount', 'transaction_details', 'transaction_amount',  'mode', 'em_service_provider_payment_details.payment_date as payout_date', 'em_service_provider_payment_details.comments') 
                ->leftjoin('em_service_provider_payments', 'em_service_provider_payments.booking_id', 'em_booking.id')
                ->leftjoin('em_service_provider_payment_details', 'em_service_provider_payment_details.booking_id', 'em_service_provider_payments.booking_id')
                ->leftjoin('users', 'users.id', 'em_booking.service_provider_id')
                ->where('em_service_provider_payments.provider_settlement', 'PAID');


        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
        
        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $bookings_qry->whereRaw('payment_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $bookings_qry->whereRaw('payment_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");

        $bookings =   $bookings_qry->groupBy('em_booking.id')->orderby($orderby,$ordermode)->get();
        
        $bookings_excel = [];
        if (! empty($bookings)) {
            foreach ($bookings as $rev) {
                
                $created_date = $rev->created_at;
                $my_date = strtotime($created_date);
                $created_date = date("Y-m-d h:i:a", $my_date);
                
                $bookings_excel[] = [
                    "Booking No" => $rev->ref_no,
                    "Amount" => $rev->transaction_amount,
                    "Details" => $rev->transaction_details,
                    "Commission" => $rev->commission_amount,
                    "Provider" => $rev->mobile,
                    "Payout Date" => $rev->payout_date,
                    "Mode" => $rev->mode,
                    "Comments" => $rev->comments,
                ];
            }
        }

        header("Content-Type: text/plain");
        $flag = false;
        foreach ($bookings_excel as $row) {
            if (! $flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            echo implode("\t", array_values($row)) . "\r\n";
        }
        exit();

    }

    // Bookings
    public function viewBookingPayments()
    {
        if(Auth::check()){
            $status = 0;
            return view('admin.booking_payments')->with('status', $status);
        }else{
            return redirect('/admin/login');
        }
    }

    public function getBookingPayments(Request $request)
    {
        $limit = $request->get('length', 10);
        $page_no = $request->get('start', 0);

        $mindate = $request->get('minDateFilter', '');
        $maxdate = $request->get('maxDateFilter', '');
       
        $columns = $request->get('columns', []);
        $dir = $request->input('order.0.dir');
        $orderby = $columns[$request->input('order.0.column')]['name'];

        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns', []);


        $status = $request->get('status', '0');

        $bookings_qry = Booking::select('em_booking.id', 'ref_no', 'em_booking.user_id', 'em_booking.service_provider_id', 
            'sub_total', 'em_booking.total_amount', 'job_date', 'job_slot', 
            'user_address_id', 'payment_mode', 'em_booking.payment_date', 'transaction_id', 'rating', 'rating_comment', 'rated_date', 'em_slots.slot_name as name', DB::RAW('(SELECT name FROM em_sub_category mcc WHERE mcc.id = em_booking.`sub_category_id`) AS main_category_name'), 'provider_settlement',
            'code', 'commission_percentage', 'commission_amount', 'em_service_provider_payments.total_amount as servicer_amount',
            'u.name as user_name', 'sp.name as provider_name')
                ->leftjoin('em_slots', \DB::raw("FIND_IN_SET(em_slots.id, em_booking.job_slot)"),">",\DB::raw("'0'"))
                ->leftjoin('em_service_provider_payments', 'em_service_provider_payments.booking_id', 'em_booking.id')
                ->leftjoin('users as u', 'u.id', 'em_booking.user_id')
                ->leftjoin('users as sp', 'sp.id', 'em_booking.service_provider_id');
        //->where('em_booking.service_provider_id', $userid);

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $bookings_qry->whereRaw('payment_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $bookings_qry->whereRaw('payment_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
        
        $bookings =   $bookings_qry->groupBy('em_booking.id')->orderby($orderby,$ordermode)
            ->skip($page_no)->limit($limit)
            ->get();

        $totalData = DB::table('em_booking')->select('em_booking.id')
            ->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))")->get();

        if(!empty($totalData)) {
            $totalData = $totalData->toArray();
            $totalData = count($totalData);
        }
        $totalFiltered = $totalData; 
        $totalFilteredqry  = $bookings_qry = Booking::select('em_booking.id', 'ref_no', 'em_booking.user_id', 'em_booking.service_provider_id', 
            'sub_total', 'em_booking.total_amount', 'job_date', 'job_slot', 
            'user_address_id', 'payment_mode', 'em_booking.payment_date', 'transaction_id', 'rating', 'rating_comment', 'rated_date', 'em_slots.slot_name as name', DB::RAW('(SELECT name FROM em_sub_category mcc WHERE mcc.id = em_booking.`sub_category_id`) AS main_category_name'), 'provider_settlement',
            'code', 'commission_percentage', 'commission_amount', 'em_service_provider_payments.total_amount as servicer_amount',
            'u.name as user_name', 'sp.name as provider_name')
                ->leftjoin('em_slots', \DB::raw("FIND_IN_SET(em_slots.id, em_booking.job_slot)"),">",\DB::raw("'0'"))
                ->leftjoin('em_service_provider_payments', 'em_service_provider_payments.booking_id', 'em_booking.id')
                ->leftjoin('users as u', 'u.id', 'em_booking.user_id')
                ->leftjoin('users as sp', 'sp.id', 'em_booking.service_provider_id');
                
        //->where('em_booking.service_provider_id', $userid);

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $totalFilteredqry->whereRaw('payment_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $totalFilteredqry->whereRaw('payment_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $totalFilteredqry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $totalFilteredqry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
        
        $totalFiltered  =   $totalFilteredqry->orderby($orderby,$ordermode)->count();
/*
        if(!empty($totalFilteredcnt)) {
            $totalFilteredcnt = $totalFilteredcnt->toArray();
            $totalFiltered = count($totalFilteredcnt);
        }  */

        $data = [];
        if(!empty($bookings))    { 
            $datum = $bookings->toArray(); //echo "<pre>"; print_r($datum); exit;
            foreach ($bookings as $k => $post)
            {   
                $nestedData[] = $post->id;
                $nestedData['id'] = $post->id;
                $nestedData['ref_no'] = $post->ref_no;
                $nestedData['user_id'] = $post->user_id;
                $nestedData['user_name'] = $post->user_name;
                $nestedData['sub_total'] = $post->sub_total;
                $nestedData['total_amount'] = $post->total_amount;
                $nestedData['service_provider_id'] = $post->service_provider_id; 
                $nestedData['provider_name'] = $post->provider_name;
                $nestedData['rated_date'] = $post->rated_date;
                $nestedData['main_category_name'] = $post->main_category_name;
                $nestedData['name'] = $post->name;
                $nestedData['job_date'] = $post->job_date;  
                $nestedData['job_slot'] = $post->job_slot;
                $nestedData['user_address_id'] = $post->user_address_id;  
                $nestedData['rating_comment'] = $post->rating_comment;
                $nestedData['rating'] = $post->rating;  
                $nestedData['transaction_id'] = $post->transaction_id;  
                $nestedData['payment_date'] = $post->payment_date;  
                $nestedData['payment_mode'] = $post->payment_mode;   
                $nestedData['code'] = $post->code;  
                $nestedData['commission_percentage'] = $post->commission_percentage;  
                $nestedData['commission_amount'] = $post->commission_amount;  
                $nestedData['servicer_amount'] = $post->servicer_amount;
                $nestedData['provider_settlement'] = $post->provider_settlement;  
                $data[] = $nestedData;
            }
        }
    

        $json_data = array(
                "draw"            => intval($request->input('draw')),  
                "recordsTotal"    => intval($totalData),  
                "recordsFiltered" => intval($totalFiltered), 
                "data"            => $data   
                );
        
        echo json_encode($json_data); 

    }

    public function getBookingPaymentsExcel(Request $request)
    {
        $limit = $request->get('length', 10);
        $page_no = $request->get('start', 0);

        $mindate = $request->get('minDateFilter', '');
        $maxdate = $request->get('maxDateFilter', '');
       
        $columns = $request->get('columns', []);
        $dir = $request->input('order.0.dir');
        $orderby = $columns[$request->input('order.0.column')]['name'];

        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns', []);


        $status = $request->get('status', '0');

        $bookings_qry = Booking::select('em_booking.id', 'ref_no', 'em_booking.user_id', 'em_booking.service_provider_id', 
            'sub_total', 'em_booking.total_amount', 'job_date', 'job_slot', 
            'user_address_id', 'payment_mode', 'em_booking.payment_date', 'transaction_id', 'rating', 'rating_comment', 'rated_date', 'em_slots.slot_name as name', DB::RAW('(SELECT name FROM em_sub_category mcc WHERE mcc.id = em_booking.`sub_category_id`) AS main_category_name'), 'provider_settlement',
            'code', 'commission_percentage', 'commission_amount', 'em_service_provider_payments.total_amount as servicer_amount','u.name as user_name', 'sp.name as provider_name')
                ->leftjoin('em_slots', \DB::raw("FIND_IN_SET(em_slots.id, em_booking.job_slot)"),">",\DB::raw("'0'"))
                ->leftjoin('em_service_provider_payments', 'em_service_provider_payments.booking_id', 'em_booking.id')
                ->leftjoin('users as u', 'u.id', 'em_booking.user_id')
                ->leftjoin('users as sp', 'sp.id', 'em_booking.service_provider_id');
        //->where('em_booking.service_provider_id', $userid);

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $bookings_qry->whereRaw('payment_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $bookings_qry->whereRaw('payment_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $orderby = 'em_booking.id'; $ordermode = 'DESC';
        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
        
        $bookings =   $bookings_qry->groupBy('em_booking.id')->orderby($orderby,$ordermode)->get(); 
          
        $bookings_excel = [];
        if (! empty($bookings)) {
            foreach ($bookings as $rev) {
                                                         
                $created_date = $rev->created_at;
                $my_date = strtotime($created_date);
                $created_date = date("Y-m-d h:i:a", $my_date);

                $bookings_excel[] = [
                    "Booking No" => $rev->ref_no,
                    "Service Provider" => $rev->service_provider_id,
                    "Customer" => $rev->user_id,
                    "Sub Total" => $rev->sub_total,
                    "Total" => $rev->total_amount,
                    "Job Date" => $rev->job_date,
                    "Job Slot" => $rev->slot_name,
                    "Payment Date" => $rev->payment_date,
                    "Payment Mode" => $rev->payment_mode,
                    "Commmission Percentage" => $rev->commission_percentage,
                    "Commission Amount" => $rev->commission_amount,
                    "Servicer Amount" => $rev->total_amount,
                ];
            }
        }

        header("Content-Type: text/plain");
        $flag = false;
        foreach ($bookings_excel as $row) {
            if (! $flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            echo implode("\t", array_values($row)) . "\r\n";
        }
        exit();

    }

    public function postSavePayout(Request $request) {
        if(Auth::check()){
            $booking_id = $request->booking_id;
            $payment_mode = $request->payment_mode;
            $amount = $request->amount;
            $payout_date = $request->payout_date;
            $transaction_details = $request->transaction_details;
            $comments = $request->comments;

            $validator = Validator::make($request->all(), [
                'booking_id' => 'required',
                'payment_mode' => 'required',
                'amount' => 'required',
                'payout_date' => 'required',
                'transaction_details' => 'required',
                'comments' => 'required',
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Invalid Inputs"

                ]);
            }

            $service_provider_id = DB::table('em_booking')->where('id', $booking_id)->value('service_provider_id');

            $vendorPayment = new ProviderPaymentDetails;

            $vendorPayment->booking_id = $booking_id;

            $vendorPayment->service_provider_id = $service_provider_id;

            $vendorPayment->transaction_details = $transaction_details;

            $vendorPayment->transaction_amount = $amount;

            $vendorPayment->mode = $payment_mode;

            $vendorPayment->payment_date = date('Y-m-d', strtotime($payout_date));

            $vendorPayment->comments = $comments;

            $vendorPayment->save();

            DB::table('em_service_provider_payments')->where('booking_id', $booking_id)
                    ->update(['provider_settlement'=>'PAID', 'remarks'=>$transaction_details.' '.$comments, 'updated_at'=>date('Y-m-d H:i:s')]);          

            $user = User::find($service_provider_id); 

            $fcmid = $user->fcm_id;

            $message = 'Your Payment Made -'.$transaction_details.' '.$comments;

            $title = 'Payment';

            //$fcmadd = ['type' => "5", 'user_id' => $user->id];

            $fcmMsg = array("fcm" => array("notification" => array(
                    "title" => $title,
                    "body" => $message,
                    "type" => "11",
                  )));

            CommonController::push_notification($user->id, $fcmMsg);

            //CommonController::pushSendUserNotification($fcmid, $message, $title, $user->id);

            if(!empty($vendorPayment)){

                return response()->json(['status' => 'SUCCESS', 'message' => 'Payment Made Successfully']);

            }else{

                return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    // Bookings
    public function viewBookings()
    {
        if(Auth::check()){
            $status = 0;
            return view('admin.bookings')->with('status', $status);
        }else{
            return redirect('/admin/login');
        }
    }

    public function getBookings(Request $request)
    {
        $limit = $request->get('length', 10);
        $page_no = $request->get('start', 0);

        $mindate = $request->get('minDateFilter', '');
        $maxdate = $request->get('maxDateFilter', '');
       
        $columns = $request->get('columns', []);
        $dir = $request->input('order.0.dir');
        $orderby = $columns[$request->input('order.0.column')]['name'];

        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns', []);


        $status = $request->get('status', '0');

        if(empty($status)) {
            $status = 0;        // 0 - pending 1 - ongoing('ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR') 2 - completed 3 - cancelled

            // 1 - ongoing('PENDING', ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR') 2 - completed 3 - cancelled
        }

        $user_bookings_qry  = Booking::leftjoin('users as u', 'u.id', 'em_booking.user_id')
            ->leftjoin('users as sr', 'sr.id', 'em_booking.service_provider_id');
        if($status == 4) {
            $user_bookings_qry->where('em_booking.status', 'PENDING');
        }   else if( $status == 1) {
            $user_bookings_qry->whereIn('em_booking.status', ['PENDING', 'ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR']);
        }   else if( $status == 2) {
            $user_bookings_qry->where('em_booking.status', 'COMPLETED');
        }   else if( $status == 3) {
            $user_bookings_qry->where('em_booking.status', 'CANCELLED');
        }   else if( $status == 0) {
            //$user_bookings_qry->where('status', 'CANCELLED');
        }

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $user_bookings_qry->whereRaw('job_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $user_bookings_qry->whereRaw('job_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $user_bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $user_filtered_qry = $user_bookings_qry;

        $user_bookings = $user_bookings_qry->select('em_booking.*')->orderby('job_date','DESC')->skip($page_no)->take($limit)->get();

       // $product =  $productqry->orderby($orderby, $dir)->offset($start)->limit($limit)->get();

        $totalData = DB::table('em_booking')->select('em_booking.id')->get();

        if(!empty($totalData)) {
            $totalData = $totalData->toArray();
            $totalData = count($totalData);
        }
        $totalFiltered = $totalData; 
        $totalFilteredqry  = Booking::leftjoin('users as u', 'u.id', 'em_booking.user_id')
            ->leftjoin('users as sr', 'sr.id', 'em_booking.user_id');
        if($status == 4) {
            $totalFilteredqry->where('em_booking.status', 'PENDING');
        }   else if( $status == 1) {
            $totalFilteredqry->whereIn('em_booking.status', ['PENDING', 'ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR']);
        }   else if( $status == 2) {
            $totalFilteredqry->where('em_booking.status', 'COMPLETED');
        }   else if( $status == 3) {
            $totalFilteredqry->where('em_booking.status', 'CANCELLED');
        }   else if( $status == 0) {
            //$totalFilteredqry->where('status', 'CANCELLED');
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $totalFilteredqry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $totalFilteredqry->whereRaw('job_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $totalFilteredqry->whereRaw('job_date <= ?', [$maxdate]);
        }

        $totalFilteredcnt = $totalFilteredqry->select('em_booking.id')->get();
        if(!empty($totalFilteredcnt)) {
            $totalFilteredcnt = $totalFilteredcnt->toArray();
            $totalFiltered = count($totalFilteredcnt);
        }  
    
        $data = [];
        if(!empty($user_bookings))    { 
            $datum = $user_bookings->toArray();// echo "<pre>"; print_r($datum); exit;
            foreach ($user_bookings as $k => $post)
            {   
                $nestedData['id'] = $post->id;
                $nestedData['ref_no'] = $post->ref_no;
                $nestedData['user_id'] = $post->user_id;
                $nestedData['service_provider_id'] = $post->service_provider_id;
                
                $nestedData['user_name'] = $datum[$k]['customer']['name'];
                $nestedData['service_provider_name'] = isset($datum[$k]['service_provider']['name']) ? $datum[$k]['service_provider']['name'] : '';

                $nestedData['user_reg_no'] = $datum[$k]['customer']['reg_no'];
                $nestedData['service_provider_reg_no'] = isset($datum[$k]['service_provider']['reg_no']) ? $datum[$k]['service_provider']['reg_no'] : '';

                $nestedData['sub_total_amount'] = $post->sub_total_amount;
                $nestedData['total_amount'] = $post->total_amount;
                $nestedData['job_date'] = $post->job_date;  
                $nestedData['job_slot'] = $post->job_slot;
                $nestedData['is_emergency'] = $post->is_emergency;  
                $nestedData['location_type'] = $post->location_type;
                $nestedData['status'] = $post->status;  
                $nestedData['payment_status'] = $post->payment_status;  
                $nestedData['payment_date'] = $post->payment_date;  
                $nestedData['payment_mode'] = $post->payment_mode;  
                $data[] = $nestedData;
            }
        }
    

        $json_data = array(
                "draw"            => intval($request->input('draw')),  
                "recordsTotal"    => intval($totalData),  
                "recordsFiltered" => intval($totalFiltered), 
                "data"            => $data   
                );
        
        echo json_encode($json_data); 

    }

    public function getBookingsExcel(Request $request)    { 
        if (Auth::check()) {
            
            $input = $request->all();
            $start = $input['start'];
            $length = $input['length'];

            $input = $request->all(); 
            $columns = $request->get('columns'); 

            $status = $request->get('status', '0');

            $mindate = $request->get('minDateFilter', '');
            $maxdate = $request->get('maxDateFilter', '');

            if(empty($status)) {
                $status = 0; 
            }

            $user_bookings_qry  = Booking::leftjoin('users as u', 'u.id', 'em_booking.user_id')
                ->leftjoin('users as sr', 'sr.id', 'em_booking.service_provider_id');
            if($status == 4) {
                $user_bookings_qry->where('em_booking.status', 'PENDING');
            }   else if( $status == 1) {
                $user_bookings_qry->whereIn('em_booking.status', ['PENDING', 'ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR']);
            }   else if( $status == 2) {
                $user_bookings_qry->where('em_booking.status', 'COMPLETED');
            }   else if( $status == 3) {
                $user_bookings_qry->where('em_booking.status', 'CANCELLED');
            }   else if( $status == 0) { 
            }

            if(!empty(trim($mindate))) {
                $mindate = date('Y-m-d', strtotime($mindate));

                $user_bookings_qry->whereRaw('job_date >= ?', [$mindate]);

            }
            if(!empty(trim($maxdate))) {
                $maxdate = date('Y-m-d', strtotime($maxdate));

                $user_bookings_qry->whereRaw('job_date <= ?', [$maxdate]);
            }

            if(count($columns)>0) {
                foreach ($columns as $key => $value) {
                    if(!empty($value['search']['value']) && !empty($value['name'])) {
                        $user_bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                    }
                }
            } 

            $bookings = $user_bookings_qry->select('em_booking.*', 'u.name as user_name', 'sr.name as provider_name')->orderby('job_date','DESC')->get();
         
          
            $bookings_excel = [];
            if (! empty($bookings)) {
                foreach ($bookings as $rev) {
                    
                    $created_date = $rev->created_at;
                    $my_date = strtotime($created_date);
                    $created_date = date("Y-m-d h:i:a", $my_date);

                    $bookings_excel[] = [
                        "User" => $rev->user_name,
                        "Service Provider" => $rev->provider_name,
                        "Amount" => $rev->total_amount,
                        "Job Date Slot" => $rev->job_date,
                        "Status" => $rev->status,
                        "Payment Status" => $rev->payment_status,
                        "Payment Date" => $rev->payment_date,
                        "Payment Mode" => $rev->payment_mode,
                    ];
                }
            }

            header("Content-Type: text/plain");
            $flag = false;
            foreach ($bookings_excel as $row) {
                if (! $flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            exit();
          
        } else {
            return redirect('/admin/login');
        }
    }

    public function viewBookingDetail($id) {
        if(Auth::check()){ //with('bookServices', 'bookServices.bookSubServices')
            $bookings = Booking::with('bookItems')
                ->where('id', $id)
                ->first();

            if(!empty($bookings)) {
               $bookings = $bookings->toArray(); 
            } 
            //echo "<pre>"; print_r($bookings); exit;
            return view('admin.view_booking')->with('normal_bookings', $bookings);
        }else{
            return redirect('/admin/login');
        }

    }

    // Users Bookings
    // Bookings
    public function viewUserBookings($id, $code)
    {
        if(Auth::check()){
            $status = 0;
            return view('admin.userbookings')->with('status', $status)->with('id', $id)->with('code', $code);
        }else{
            return redirect('/admin/login');
        }
    }

    public function getUserBookings($id, $code, Request $request)
    {
        $limit = $request->get('length', 10);
        $page_no = $request->get('start', 0);

        $mindate = $request->get('minDateFilter', '');
        $maxdate = $request->get('maxDateFilter', '');
       
        $columns = $request->get('columns', []);
        $dir = $request->input('order.0.dir');
        $orderby = $columns[$request->input('order.0.column')]['name'];

        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns', []);


        $status = $request->get('status', '0');

        if(empty($status)) {
            $status = 0;        // 0 - pending 1 - ongoing('ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR') 2 - completed 3 - cancelled

            // 1 - ongoing('PENDING', ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR') 2 - completed 3 - cancelled
        }

        $user_bookings_qry  = Booking::leftjoin('users as u', 'u.id', 'em_booking.user_id')
            ->leftjoin('users as sr', 'sr.id', 'em_booking.service_provider_id')
            ->where('user_id',$id);
        if($status == 4) {
            $user_bookings_qry->where('em_booking.status', 'PENDING');
        }   else if( $status == 1) {
            $user_bookings_qry->whereIn('em_booking.status', ['PENDING', 'ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR']);
        }   else if( $status == 2) {
            $user_bookings_qry->where('em_booking.status', 'COMPLETED');
        }   else if( $status == 3) {
            $user_bookings_qry->where('em_booking.status', 'CANCELLED');
        }   else if( $status == 0) {
            //$user_bookings_qry->where('status', 'CANCELLED');
        }

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $user_bookings_qry->whereRaw('job_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $user_bookings_qry->whereRaw('job_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $user_bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $user_filtered_qry = $user_bookings_qry;

        $user_bookings = $user_bookings_qry->select('em_booking.*')->orderby('job_date','DESC')->skip($page_no)->take($limit)->get();

       // $product =  $productqry->orderby($orderby, $dir)->offset($start)->limit($limit)->get();

        $totalData = DB::table('em_booking')->select('em_booking.id')->get();

        if(!empty($totalData)) {
            $totalData = $totalData->toArray();
            $totalData = count($totalData);
        }
        $totalFiltered = $totalData; 
        $totalFilteredqry  = Booking::leftjoin('users as u', 'u.id', 'em_booking.user_id')
            ->leftjoin('users as sr', 'sr.id', 'em_booking.user_id')->where('user_id', $id);
        if($status == 4) {
            $totalFilteredqry->where('em_booking.status', 'PENDING');
        }   else if( $status == 1) {
            $totalFilteredqry->whereIn('em_booking.status', ['PENDING', 'ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR']);
        }   else if( $status == 2) {
            $totalFilteredqry->where('em_booking.status', 'COMPLETED');
        }   else if( $status == 3) {
            $totalFilteredqry->where('em_booking.status', 'CANCELLED');
        }   else if( $status == 0) {
            //$totalFilteredqry->where('status', 'CANCELLED');
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $totalFilteredqry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $totalFilteredqry->whereRaw('job_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $totalFilteredqry->whereRaw('job_date <= ?', [$maxdate]);
        }

        $totalFilteredcnt = $totalFilteredqry->select('em_booking.id')->get();
        if(!empty($totalFilteredcnt)) {
            $totalFilteredcnt = $totalFilteredcnt->toArray();
            $totalFiltered = count($totalFilteredcnt);
        }  
    
        $data = [];
        if(!empty($user_bookings))    { 
            $datum = $user_bookings->toArray(); //echo "<pre>"; print_r($datum); exit;
            foreach ($user_bookings as $k => $post)
            {   
                $nestedData['id'] = $post->id;
                $nestedData['ref_no'] = $post->ref_no;
                $nestedData['user_id'] = $post->user_id;
                $nestedData['service_provider_id'] = $post->service_provider_id;
                
                $nestedData['user_id'] = $datum[$k]['customer']['name'];
                $nestedData['service_provider_id'] = $datum[$k]['service_provider']['name'];

                $nestedData['sub_total_amount'] = $post->sub_total_amount;
                $nestedData['total_amount'] = $post->total_amount;
                $nestedData['job_date'] = $post->job_date;  
                $nestedData['job_slot'] = $post->job_slot;
                $nestedData['is_emergency'] = $post->is_emergency;  
                $nestedData['location_type'] = $post->location_type;
                $nestedData['status'] = $post->status;  
                $nestedData['payment_status'] = $post->payment_status;  
                $nestedData['payment_date'] = $post->payment_date;  
                $nestedData['payment_mode'] = $post->payment_mode;  
                $data[] = $nestedData;
            }
        }
    

        $json_data = array(
                "draw"            => intval($request->input('draw')),  
                "recordsTotal"    => intval($totalData),  
                "recordsFiltered" => intval($totalFiltered), 
                "data"            => $data   
                );
        
        echo json_encode($json_data); 

    }

    public function viewUserBookingDetail($id) {
        if(Auth::check()){
            $normal_bookings = Booking::with('bookServices', 'bookServices.bookSubServices')
                ->where('id', $id)
                ->first();

            if(!empty($normal_bookings)) {
               $normal_bookings = $normal_bookings->toArray(); 
            }
//echo "<pre>"; print_r($normal_bookings); exit;
            return view('admin.view_normal_booking')->with('normal_bookings', $normal_bookings);
        }else{
            return redirect('/admin/login');
        }

    }

    public function viewNormalBookingDetail($id, $uid, $code) {
        if(Auth::check()){
            $normal_bookings = Booking::with('bookServices', 'bookServices.bookSubServices')
                ->where('id', $id)
                ->first();

            if(!empty($normal_bookings)) {
               $normal_bookings = $normal_bookings->toArray(); 
            }
//echo "<pre>"; print_r($normal_bookings); exit;
            return view('admin.view_user_normal_booking')->with('normal_bookings', $normal_bookings)
                ->with('uid', $uid)->with('code', $code);
        }else{
            return redirect('/admin/login');
        }
    }

    // Servicers
    public function servicerPage($status='')
    {
        if(Auth::check()){
            return view('admin.servicers')->with('status', $status);
        }else{
            return redirect('/admin/login');
        }
    }

    public function getServicers(Request $request)
    {
        $status = $request->status;

        $servicersqry = User::where('user_type', 'SERVICEPROVIDER');
        if(!empty($status)) {
            if($status == 'pending') {
                $servicersqry->whereRaw('approve_status IS NULL')->where('step', 6);
            }
        } else {
            $servicersqry->where('approve_status', 'APPROVED')->where('step', 6);
        }
        $servicers =  $servicersqry->orderby('id', 'desc')->get(); 

        return Datatables::of($servicers)->make(true);

    }

    public function viewServicer($id,$code){

        if(Auth::check()){
        $user = User::find($id);
        $user_array = User::find($id)->toArray();

        $servicer = ServiceProvider::where('user_id',$id)->first();
        $servicer_array = ServiceProvider::where('user_id',$id)->first()->toArray();  
        $servicer_array['documents'] = CommonController::getDocuments($id); 
        $servicer_sub_cat_ids = $servicer->sub_category_ids;
        $subcats = [];
        if(!empty(trim($servicer_sub_cat_ids))) {
            $subcats = explode(',', $servicer_sub_cat_ids);
            $subcats = array_filter($subcats);
        }

        SubCategory::$service_provider_id = $id;

        $catids = [];
        if(!empty($servicer->category_id)) {
            $catids = explode(',', $servicer->category_id);
        }
        /*$subcategories = SubCategory::with('services', 'services.subServices', 'services.subServices.servicersdetails')
                ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                ->where('em_sub_category.status', 'ACTIVE')
                ->whereIn('em_sub_category.category_id',  $catids)
                ->whereIn('em_sub_category.id', $subcats)
                ->select('em_sub_category.*', 'em_category.name as category_name')
                ->orderby('position', 'asc')
                ->get();*/
        Category::$service_provider_id = $id;
        Category::$subcats = $subcats;
        $categories = Category::with('subCategories')
            ->where('em_category.status', 'ACTIVE')
            ->whereIn('em_category.id',  $catids)
            ->orderby('position', 'asc')
            ->get();

        //echo "<pre>"; print_r($categories); exit;

        return view('admin.servicer_view')->with('user',$user)->with('servicer',$servicer)
            ->with('user_array',$user_array)->with('servicer_array',$servicer_array)->with('categories',$categories);

        }else{
            return redirect('/admin/login');
        }
    }

    public function editServicer($id,$code){
        if(Auth::check()){
        $user = User::find($id);
        return view('admin.servicer_edit')->with('user',$user);
        }else{
            return redirect('/admin/login');
        }
    }

    public function putServicer(Request $request){
        if(Auth::check()){
        $userid = $request->user_id;
        $status = $request->status;

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Invalid Inputs"

            ]);
        }

        $user = User::find($userid);
        $user->status = $status;
        $user->save();

        if(!empty($user)){

            return response()->json(['status' => 'SUCCESS', 'message' => 'Servicer Status Updated']);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
        }
        }else{
            return redirect('/admin/login');
        }
    }

    public function putApproveServicer(Request $request) {
        if(Auth::check()){
            $userid = $request->user_id;
            $approve_status = $request->approve_status;

            $validator = Validator::make($request->all(), [
                'approve_status' => 'required'
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Invalid Inputs"

                ]);
            }

            $user = User::find($userid);
            $user->approve_status = $approve_status;
            $user->save();

            $fcmid = $user->fcm_id;

            $message = 'Your Account has been .'.$approve_status;

            $title = 'Account '.$approve_status;

            //$fcmadd = ['type' => "5", 'user_id' => $user->id];

            $fcmMsg = array("fcm" => array("notification" => array(
                    "title" => $title,
                    "body" => $message,
                    "type" => "5",
                  )));

            CommonController::push_notification($user->id, $fcmMsg);

            //CommonController::pushSendUserNotification($fcmid, $message, $title, $user->id);

            if(!empty($user)){

                return response()->json(['status' => 'SUCCESS', 'message' => 'Servicer Approve Status Updated']);

            }else{

                return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
            }
        }else{
            return redirect('/admin/login');
        }
    }

    // Servicers Bookings
    // Bookings
    public function viewServicerBookings($id, $code)
    {
        if(Auth::check()){
            $status = 0;
            return view('admin.servicerbookings')->with('status', $status)->with('id', $id)->with('code', $code);
        }else{
            return redirect('/admin/login');
        }
    }

    public function getServicerBookings($id, $code, Request $request)
    {
        $limit = $request->get('length', 10);
        $page_no = $request->get('start', 0);

        $mindate = $request->get('minDateFilter', '');
        $maxdate = $request->get('maxDateFilter', '');
       
        $columns = $request->get('columns', []);
        $dir = $request->input('order.0.dir');
        $orderby = $columns[$request->input('order.0.column')]['name'];

        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns', []);


        $status = $request->get('status', '0');

        if(empty($status)) {
            $status = 0;        // 0 - pending 1 - ongoing('ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR') 2 - completed 3 - cancelled

            // 1 - ongoing('PENDING', ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR') 2 - completed 3 - cancelled
        }

        $user_bookings_qry  = Booking::leftjoin('users as u', 'u.id', 'em_booking.user_id')
            ->leftjoin('users as sr', 'sr.id', 'em_booking.service_provider_id')
            ->where('service_provider_id',$id);
        if($status == 4) {
            $user_bookings_qry->where('em_booking.status', 'PENDING');
        }   else if( $status == 1) {
            $user_bookings_qry->whereIn('em_booking.status', ['PENDING', 'ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR']);
        }   else if( $status == 2) {
            $user_bookings_qry->where('em_booking.status', 'COMPLETED');
        }   else if( $status == 3) {
            $user_bookings_qry->where('em_booking.status', 'CANCELLED');
        }   else if( $status == 0) {
            //$user_bookings_qry->where('status', 'CANCELLED');
        }

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $user_bookings_qry->whereRaw('job_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $user_bookings_qry->whereRaw('job_date <= ?', [$maxdate]);
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $user_bookings_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        $user_filtered_qry = $user_bookings_qry;

        $user_bookings = $user_bookings_qry->select('em_booking.*')->orderby('job_date','DESC')->skip($page_no)->take($limit)->get();

       // $product =  $productqry->orderby($orderby, $dir)->offset($start)->limit($limit)->get();

        $totalData = DB::table('em_booking')->select('em_booking.id')->get();

        if(!empty($totalData)) {
            $totalData = $totalData->toArray();
            $totalData = count($totalData);
        }
        $totalFiltered = $totalData; 
        $totalFilteredqry  = Booking::leftjoin('users as u', 'u.id', 'em_booking.user_id')
            ->leftjoin('users as sr', 'sr.id', 'em_booking.service_provider_id')->where('service_provider_id', $id);
        if($status == 4) {
            $totalFilteredqry->where('em_booking.status', 'PENDING');
        }   else if( $status == 1) {
            $totalFilteredqry->whereIn('em_booking.status', ['PENDING', 'ACCEPTED','STARTED','INPROGRESS','UNABLETOREPAIR']);
        }   else if( $status == 2) {
            $totalFilteredqry->where('em_booking.status', 'COMPLETED');
        }   else if( $status == 3) {
            $totalFilteredqry->where('em_booking.status', 'CANCELLED');
        }   else if( $status == 0) {
            //$totalFilteredqry->where('status', 'CANCELLED');
        }

        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value']) && !empty($value['name'])) {
                    $totalFilteredqry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d', strtotime($mindate));

            $totalFilteredqry->whereRaw('job_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d', strtotime($maxdate));

            $totalFilteredqry->whereRaw('job_date <= ?', [$maxdate]);
        }

        $totalFilteredcnt = $totalFilteredqry->select('em_booking.id')->get();
        if(!empty($totalFilteredcnt)) {
            $totalFilteredcnt = $totalFilteredcnt->toArray();
            $totalFiltered = count($totalFilteredcnt);
        }  
    
        $data = [];
        if(!empty($user_bookings))    { 
            $datum = $user_bookings->toArray(); //echo "<pre>"; print_r($datum); exit;
            foreach ($user_bookings as $k => $post)
            {   
                $nestedData['id'] = $post->id;
                $nestedData['ref_no'] = $post->ref_no;
                $nestedData['user_id'] = $post->user_id;
                $nestedData['service_provider_id'] = $post->service_provider_id;
                
                $nestedData['user_id'] = $datum[$k]['customer']['name'];
                $nestedData['service_provider_id'] = $datum[$k]['service_provider']['name'];

                $nestedData['sub_total_amount'] = $post->sub_total_amount;
                $nestedData['total_amount'] = $post->total_amount;
                $nestedData['job_date'] = $post->job_date;  
                $nestedData['job_slot'] = $post->job_slot;
                $nestedData['is_emergency'] = $post->is_emergency;  
                $nestedData['location_type'] = $post->location_type;
                $nestedData['status'] = $post->status;  
                $nestedData['payment_status'] = $post->payment_status;  
                $nestedData['payment_date'] = $post->payment_date;  
                $nestedData['payment_mode'] = $post->payment_mode;  
                $data[] = $nestedData;
            }
        }
    

        $json_data = array(
                "draw"            => intval($request->input('draw')),  
                "recordsTotal"    => intval($totalData),  
                "recordsFiltered" => intval($totalFiltered), 
                "data"            => $data   
                );
        
        echo json_encode($json_data); 

    }

    public function viewServicerBookingDetail($id, $uid, $code) {
        if(Auth::check()){
            $normal_bookings = Booking::with('bookServices', 'bookServices.bookSubServices')
                ->where('id', $id)
                ->first();

            if(!empty($normal_bookings)) {
               $normal_bookings = $normal_bookings->toArray(); 
            }
//echo "<pre>"; print_r($normal_bookings); exit;
            return view('admin.view_servicer_normal_booking')->with('normal_bookings', $normal_bookings)
                ->with('uid', $uid)->with('code', $code);
        }else{
            return redirect('/admin/login');
        }
    }

    // Credit / Debit User wallet
    /* Function: userWallet
     */
    public function userWallet()   {
        if(Auth::check()){
            $users = User::where('status', 'ACTIVE')->where('user_type', 'SERVICEPROVIDER')
                ->select('id', 'name', 'mobile')->get();
            return view('admin.user_wallet')->with('users', $users);
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: getUserWallet
        Datatable Load
     */
    public function getUserWallet(Request $request)    {
        if(Auth::check()){
            $sel_user_id = $request->sel_user_id;
            $user_wallet_qry = DB::table('em_user_wallets')
                ->leftjoin('users', 'users.id', 'em_user_wallets.user_id');

            if($sel_user_id > 0) {
                $user_wallet_qry->where('em_user_wallets.user_id', $sel_user_id);
            }
            $user_wallet = $user_wallet_qry->select('users.name as user_name', 'users.mobile', 'em_user_wallets.wallet_amount')->get();
            
            return Datatables::of($user_wallet)->make(true);
        }else{
            return redirect('/admin/login');
        }
    } 

    /* Function: postUserGetWalletAmount
        Datatable Load
     */
    public function postUserGetWalletAmount(Request $request) {
        if(Auth::check()){
            $input = $request->all(); 

            $user_id = $input['code'];

            if($user_id>0) {
                $amount = DB::table('em_user_wallets')->where('user_id', $user_id)->value('wallet_amount');
                if(empty($amount)) {
                    $amount = 0;
                }
            }   else {
                return response()->json(['status' => 'FAILED', 'message' => 'Please select the User']);
            }

            return response()->json(['status' => 'SUCCESS', 'message' => 'Wallet Amount', 'data' => ['amount'=>$amount]]);
        }else{
            return response()->json(['status' => 'FAILED', 'message' => 'Unable to get Wallet Amount']);
        }
    }

    /* Save amount to wallet */
    public function postUserWallet(Request $request)   {
        if(Auth::check()){
            $input = $request->all(); 

            $user_id = $input['user_id'];

            $type = $input['type'];

            $amount = $input['amount'];

            $description = $input['description'];

            $reason = $request->get('reason', []);
    
            $reason = implode(',', $reason);

        $wallet = UserWallet::where('user_id',$user_id)->first();

        if(empty($wallet)) {
            $wallet = new UserWallet();

            $wallet->user_id = $user_id;

            $wallet->wallet_amount = $amount;

            $wallet->status = 'ACTIVE';

            $wallet->created_at = date('Y-m-d H:i:s');

        }   else {

            if($type == "CREDIT") {
                $walletBalanceAmount = $wallet->wallet_amount + $amount; 
            }   else {
                $walletBalanceAmount = $wallet->wallet_amount - $amount; 
            }

            $wallet->wallet_amount = $walletBalanceAmount;

            $wallet->updated_at = date('Y-m-d H:i:s');

        }

        $wallet->save();

        // Add WalletPayment Details

        $walletDetails = new UserWalletDetail;

        $walletDetails->user_id = $user_id;

        $walletDetails->wallet_id = $wallet->id;

        $walletDetails->amount = $amount;
 
        $walletDetails->message = $description;

        $walletDetails->type = $type;

        $walletDetails->transaction_details = 'TXN'.date('Ymd').User::random_strings(5);

        $walletDetails->reason = $reason;

        $walletDetails->update_by = Auth::User()->id;

        $walletDetails->update_source = 'ADMIN';

        $walletDetails->wallet_date = date('Y-m-d h:m:s');
 
        $walletDetails->save();

        $fcmMsg = array("fcm" => array("notification" => array(
            "title" => "Wallet ". $type,
            "body" => "Your Wallet Amount in Emruv : ".$type.': '.$description,
            "type" => 10 
          )));
        CommonController::push_notification($user_id, $fcmMsg);
 
        return response()->json(['status' => 'SUCCESS', 'message' => 'Amount Updated to Wallet Successfully']);

        }else{
            return redirect('/admin/login');
        }
    }

    //   User wallet Transactions
    /* Function: userWallet
     */
    public function userWalletTransactions()   {
        if(Auth::check()){
            $users = User::where('status', 'ACTIVE')->where('user_type', 'SERVICEPROVIDER')->select('id', 'name', 'mobile')
                ->orderby('mobile', 'asc')->get();
            return view('admin.user_wallet_transactions')->with('users', $users);
        }else{
            return redirect('/admin/login');
        }
    }

    /* Function: getUserWalletTransactions
        Datatable Load
     */
    public function getUserWalletTransactions(Request $request)    {
        if(Auth::check()){
            /*$user_wallet = DB::table('em_user_wallets_details') 
                ->leftjoin('users', 'users.id', 'em_user_wallets_details.user_id')
                ->select('users.name as user_name', 'users.mobile', 'em_user_wallets_details.*')->get();
            return Datatables::of($user_wallet)->make(true);*/


            $input = $request->all();
            $mindate = $input['minDateFilter'];
            $maxdate = $input['maxDateFilter'];
            $user_id = $input['user_id'];
            $type = $input['type'];
 
        $where = [];

        $session_country_code = Session::get('session_country_code');
 
        $user_wallet_qry = DB::table('em_user_wallets_details') 
                ->leftjoin('users', 'users.id', 'em_user_wallets_details.user_id')
               // ->select('users.name as user_name', 'users.mobile', 'em_user_wallets_details.*')
                ->where('users.country_code', $session_country_code);

        if(!empty(trim($mindate))) {
            $mindate = date('Y-m-d H:i', strtotime($mindate));

            $user_wallet_qry->whereRaw('wallet_date >= ?', [$mindate]);

        }
        if(!empty(trim($maxdate))) {
            $maxdate = date('Y-m-d H:i', strtotime('+1 day '.$maxdate));

            $user_wallet_qry->whereRaw('wallet_date <= ?', [$maxdate]);
        } 
        if(!empty($type)) {
            $user_wallet_qry->where('type', $type);
        }
        if($user_id>0) {
            $user_wallet_qry->where('user_id', $user_id);
        } 

        $limit = $request->get('length', '10');
        $start = $request->get('start', '0');
        $dir = $request->input('order.0.dir');
        $columns = $request->get('columns');
        $order = $request->input('order.0.column');
        if(count($columns)>0) {
            foreach ($columns as $key => $value) {
                if(!empty($value['search']['value'])) {
                    $user_wallet_qry->where($value['name'], 'like', '%'.$value['search']['value'].'%');
                }
            }
        }
        $orderby = $columns[$order]['name'];
 
        $user_wallet_qry->select('users.name as user_name', 'users.mobile', 'em_user_wallets_details.*');
       
        $totalFilteredqry =  $user_wallet_qry;
        $totalFiltered = $user_wallet_qry->orderBy($orderby,$dir)->count();
        $totalfilteredamount = $user_wallet_qry->sum('em_user_wallets_details.amount'); 
        $user_wallet = $user_wallet_qry->orderBy($orderby,$dir)->offset($start)->limit($limit)->get();

        $totalordersqry = DB::table('em_user_wallets_details') 
                ->leftjoin('users', 'users.id', 'em_user_wallets_details.user_id') 
                ->where('users.country_code', $session_country_code);

        $totalData = $totalordersqry->select('em_user_wallets_details.id')->orderBy('em_user_wallets_details.id','DESC')->get();

        $totalorderamount = DB::table('em_user_wallets_details') 
                ->leftjoin('users', 'users.id', 'em_user_wallets_details.user_id') 
                ->where('users.country_code', $session_country_code) 
                ->sum('em_user_wallets_details.amount');
 
        if(!empty($totalData)) {
            $totalData = count($totalData);
        }
 
        if(!empty($totalFilteredcnt)) {
            $totalFiltered = count($totalFilteredcnt);
        }  
        
        $data = [];
        if(!empty($user_wallet))    {
            foreach ($user_wallet as $post)
            {   
                $nestedData = [];
                foreach($post as $k=>$v) {
                    $nestedData[$k] = $v;
                }
                $data[] = $nestedData;
            }
        }
        

         $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data,
                    "totalorderamount" => number_format($totalorderamount,2),
                    "totalfilteredamount" => number_format($totalfilteredamount,2),
                    );
            
        echo json_encode($json_data); 




        }else{
            return redirect('/admin/login');
        }
    } 

    /* Function: View Content Pages in App */
    public function viewContents($page) {
       
            /* Content Pages */

            if(!empty(trim($page))) {

            }   else {
                $page = 'about';
            } 
            $contents = $help_contact = '';
            $settings_qry = DB::table('em_admin_settings');
            if($page == 'about') {
                $settings_qry->select('about as content');
            }  else if($page == 'helpsupport' ) { 
                $settings_qry->select('helpcontact as content');
            }   else if($page == 'userterms' ) { 
                $settings_qry->select('user_terms_conditions as content');
            }   else if($page == 'servicerterms' ) { 
                $settings_qry->select('servicer_terms_conditions as content');
            }   else {
                $settings_qry->select('about as content');
            }

            $settings = $settings_qry->orderby('id', 'asc')->first();
            $content = $settings->content; 
            return view('user.contents')->with('page', $page)->with('contents', $content)->with('help_contact', $help_contact);
        
    }


    // User Roles
    /*
     * Function: viewUserRoles
     */
    public function viewUserRoles()
    {
        if (Auth::check()) {
            return view('admin.roles');
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: getUserRoles
     * Datatable Load
     */
    public function getUserRoles(Request $request)
    {
        if (Auth::check()) {
            $roles = UserRoles::all();
            return Datatables::of($roles)->make(true);
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: postUserRoles
     * Save into sc_userroles table
     */
    public function postUserRoles(Request $request)
    {
        if (Auth::check()) {
            $id = $request->id;
            $user_role = $request->user_role;
            $status = $request->status;

            $validator = Validator::make($request->all(), [
                'user_role' => 'required',
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            if ($id > 0) {
                $exroles = UserRoles::where('id', '!=', $id)->whereRAW('LOWER(user_role) = "'.strtolower($user_role).'"')->first();
            }   else {
                $exroles = UserRoles::whereRAW('LOWER(user_role) = "'.strtolower($user_role).'"')->first();
            }

            if(!empty($exroles)) {
                return response()->json([
                    'status' => "FAILED",
                    'message' => "Role Name Already Exists."
                ]);
            }

            if ($id > 0) {
                $role = UserRoles::find($id);
                $role->updated_at = date('Y-m-d H:i:s');
            } else {
                $role = new UserRoles();
                $role->created_at = date('Y-m-d H:i:s');

                // Last Order id
                $lastorderid = DB::table('em_userroles')
                    ->orderby('id', 'desc')->select('id')->limit(1)->get();

                if($lastorderid->isNotEmpty()) {
                    $lastorderid = $lastorderid[0]->id;
                    $lastorderid = $lastorderid + 1;
                }   else {
                    $lastorderid = 1;
                }

                $append = str_pad($lastorderid,3,"0",STR_PAD_LEFT);

                $role->ref_code = CommonController::$book_prefix.'UR'.$append;
            }

            $role->user_role = $user_role;
            $role->status = $status; 

            $role->save();
            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'User Role Saved Successfully'
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function editUserRoles(Request $request)
    {
        if (Auth::check()) {
            $role = UserRoles::where('id', $request->code)->get();
            if ($role->isNotEmpty()) {
                return response()->json([
                    'status' => 'SUCCESS',
                    'data' => $role[0],
                    'message' => 'User Role Detail'
                ]);
            } else {
                return response()->json([
                    'status' => 'FAILED',
                    'data' => [],
                    'message' => 'No User Role Detail'
                ]);
            }
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: getroleExcel
     * Loading getroleExcel page
     */
     public function getroleExcel(Request $request)
    {
        $users = User::where('user_role', 'USER')->select('users.*')->get();
        if (Auth::check()) {
            
            $input = $request->all();
            $start = $input['start'];
            $length = $input['length'];

            $input = $request->all();
            $users = UserRoles::all();
           
            $users_excel = [];
            if (! empty($users)) {
                foreach ($users as $rev) {
                    
                    $created_date = $rev->created_at;
                    $my_date = strtotime($created_date);
                    $created_date = date("Y-m-d h:i:a", $my_date);

                    $users_excel[] = [
                       
                        "Code" => $rev->ref_code,
                        "User role " => $rev->user_role,
                        
                        "Status" => $rev->status,
                                                    
                      
                    ];
                }
            }

            header("Content-Type: text/plain");
            $flag = false;
            foreach ($users_excel as $row) {
                if (! $flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            exit();
          
        } else {
            return redirect('/admin/login');
        }
    }

    // viewModules
    /*
     * Function: View All the Modules
     */
    public function viewModules()
    {
        if (Auth::check()) {
            $modules = Module::where('status', 1)->get();
            return view('admin.modules')->with('module', $modules);
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: getModules
     * Datatable Load
     */
    public function getModules(Request $request)
    {
        if (Auth::check()) {
            //$modules = Module::where('status', '<>', 0);
            $modules = Module::leftjoin('em_modules as pf', 'pf.id', 'em_modules.parent_module_fk')
                ->where('em_modules.status', '<>', 0)
                ->select('em_modules.*', 'pf.module_name as parent_module_name');
            return Datatables::of($modules)->make(true);
        } else {
            return redirect('/admin/login');
        }
    }

    public function getModule(Request $request)
    {
        if (Auth::check()) {
            $modules = Module::where('parent_module_fk', $request->parent_id)->select(DB::raw('group_concat(id) as ids'))->first()->ids;
            return $modules;
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function:post postModule
     * Save into  Module table
     */
    public function postModule(Request $request)
    {
        if (Auth::check()) {
            $id = $request->id;

            $name = $request->name;
            $parent_module_fk = $request->module_id;
            $status = $request->status;
            $rank = $request->rank;
            $icon = $request->icon;
            $url = $request->url;

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'rank' => 'required',
                'url' => 'required',
                //'icon' => 'required',
                'status' => 'required'
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            if ($id > 0) {
                $module = Module::find($id);
            } else {
                $module = new Module();
                $module->created_at = date('Y-m-d H:i:s');
            }

            $module->module_name = $name;
            $module->menu_rank = $rank;
            $module->status = $status;
            $module->icon = $icon;
            $module->url = $url;

            $module->parent_module_fk = $parent_module_fk;

            $module->save();
            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Module Saved Successfully'
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function editModule(Request $request)
    {
        if (Auth::check()) {
            $module = Module::where('id', $request->code)->get();
            if ($module->isNotEmpty()) {
                return response()->json([
                    'status' => 'SUCCESS',
                    'data' => $module[0],
                    'message' => 'Module Detail'
                ]);
            } else {
                return response()->json([
                    'status' => 'FAILED',
                    'data' => [],
                    'message' => 'No Module Detail'
                ]);
            }
        } else {
            return redirect('/admin/login');
        }
    }

     // viewRoleModuleMapping
    /*
     * Function: viewRoleModuleMapping
     */
    public function viewRoleModuleMapping()
    {
        if (Auth::check()) {

            return view('admin.role_module_mapping');
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: getUserRolesMapping
     * Datatable Load
     */
    public function getUserRolesMapping(Request $request)
    {
        if (Auth::check()) {
            $roles = UserRoles::where('status', 'ACTIVE')->get();
            return Datatables::of($roles)->make(true);
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function: getRoleModuleMapping
     * Datatable Load
     */
    public function getRoleModuleMapping(Request $request)
    {
        if (Auth::check()) { 
            $mappings = RoleModuleMapping::where('ra_role_fk', '<>', 1)->groupby('ra_role_fk');
            return Datatables::of($mappings)->make(true);
        } else {
            return redirect('/admin/login');
        }
    }

    /*
     * Function:post postRoleModuleMapping
     * Save into postRoleModuleMapping table
     */
    public function postRoleModuleMapping(Request $request)
    {
        if (Auth::check()) {
            $role_fk = $request->role_fk;

            $validator = Validator::make($request->all(), [
                'role_fk' => 'required'
            ]);

            if ($validator->fails()) {

                $msg = $validator->errors()->all();

                return response()->json([

                    'status' => "FAILED",
                    'message' => "Please check your all inputs"
                ]);
            }

            $role_fk = $request->role_fk;

            $sysrolemodule = $request['SysroleModule']['id'];

            $select_val1 = array();
            $role_access = RoleModuleMapping::where('ra_role_fk', $role_fk)->get();
            foreach ($role_access as $role_access) {
                $select_val1[] = $role_access['ra_module_fk'];
            }

            foreach ($sysrolemodule as $pos => $val) {
                if (isset($request['SysroleModule_add' . $val])) {
                    $add = 1;
                } else {
                    $add = 0;
                }

                if (isset($request['SysroleModule_edit' . $val])) {
                    $edit = 1;
                } else {
                    $edit = 0;
                }

                if (isset($request['SysroleModule_view' . $val])) {
                    $view = 1;
                } else {
                    $view = 0;
                }

                if (isset($request['SysroleModule_delete' . $val])) {
                    $delete = 1;
                } else {
                    $delete = 0;
                }

                if (isset($request['SysroleModule_list' . $val])) {
                    $list = 1;
                } else {
                    $list = 0;
                }

                if (isset($request['SysroleModule_statusupdate' . $val])) {
                    $status_update = 1;
                } else {
                    $status_update = 0;
                }

                if (isset($request['SysroleModule_aadharstatusupdate' . $val])) {
                    $aadhar_status_update = 1;
                } else {
                    $aadhar_status_update = 0;
                }

                $role_access_pa = RoleModuleMapping::where('ra_role_fk', $role_fk)->where('ra_module_fk', $val)->first();
                $id = '';
                if (! empty($role_access_pa)) {
                    $id = $role_access_pa->id;
                }
                // $select=mysqli_query($conn,"select ra_pk from role_access where ra_role_fk=$role and ra_module_fk=$val");

                if ($id > 0) {
                    $rolemodule = RoleModuleMapping::find($id);
                    $rolemodule->modified_by = Auth::User()->id;
                } else {
                    $rolemodule = new RoleModuleMapping();
                    $rolemodule->created_at = date('Y-m-d');
                    $rolemodule->created_by = Auth::User()->id;
                }
                $rolemodule->ra_role_fk = $role_fk;
                $rolemodule->ra_module_fk = $val;
                $rolemodule->ra_add = $add;
                $rolemodule->ra_edit = $edit;
                $rolemodule->ra_delete = $delete;
                $rolemodule->ra_view = $view;
                $rolemodule->ra_list = $list;
                $rolemodule->ra_status_update = $status_update;
                $rolemodule->ra_aadhar_status_update = $aadhar_status_update;
                $rolemodule->save();

                $select_val[] = $val;

                // if(mysqli_num_rows($select)>0){
                // $result=mysqli_fetch_array($select);
                // $select_val[]=$val;
                // mysqli_query($conn,"update role_access set `add`=$add, `edit`=$edit, `delete`=$delete, `view`=$view where ra_role_fk=$role and ra_module_fk=$val ");
                // }else {
                // mysqli_query("delete from role_access where ra_role_fk=$id and ra_role_fk=$val");
                // $sql="INSERT INTO `role_access` (`ra_pk`, `ra_role_fk`, `ra_module_fk`, `ra_add`, `ra_edit`, `ra_delete`, `ra_view`,`created_by_user_fk`,`created_on`,`modified_by_user_fk`,`modified_on`) VALUES (NULL, '$role', '$val', '$add', '$edit', '$delete', '$view', '$session_user_fk','$current_time','$session_user_fk','$current_time');";
                // mysqli_query($conn,$sql);

                // }
            }

            $delete_mo = array_diff($select_val1, $select_val);
            foreach ($delete_mo as $delete_mo) {
                DB::table('sc_role_access')->where('ra_role_fk', $role_fk)
                    ->where('ra_module_fk', $delete_mo)
                    ->delete();
            }

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Role Saved Successfully'
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function editRoleModuleMapping(Request $request)
    {
        if (Auth::check()) {
            $mapping = RoleModuleMapping::where('id', $request->code)->get();
            if ($mapping->isNotEmpty()) {
                return response()->json([
                    'status' => 'SUCCESS',
                    'data' => $mapping[0],
                    'message' => 'Mapping Detail'
                ]);
            } else {
                return response()->json([
                    'status' => 'FAILED',
                    'data' => [],
                    'message' => 'No Mapping Detail'
                ]);
            }
        } else {
            return redirect('/admin/login');
        }
    }

    public function viewRoleAccess($role_id)
    {
        if (Auth::check()) {

            $modules = Module::where('parent_module_fk', '=', 0)->where('status', 1)->get();

            $allmodules = Module::where('status', 1)->pluck('module_name', 'id')->all();
            $role = UserRoles::find($role_id);

            $role_name = $role->user_role;
            // echo $role_id;
            // echo $role_name;
            // exit;
            return view('admin.update_role_access')->with('role_fk', $role_id)->with('role_name', $role_name);
        } else {
            return redirect('/admin/login');
        }
    }

    public static function getRights() {

        $url_name = $_SERVER['REQUEST_URI'];
        $dd = explode('/', $url_name); 
        $course_pages_access = ['subjects'=>['list'=>0], 'packages'=>['list'=>0], 'chapters'=>['list'=>0], 'topics'=>['list'=>0], 
            'docs'=>['list'=>0], 'videos'=>['list'=>0], 'tests'=>['list'=>0]];
        if (isset($dd[3])) {  

            $search = session()->get('module');  

            if(empty($search)) {
                $search = [];
            }
 //echo "<pre>"; print_r($search); exit;
            $act_page = str_replace("_", " ", $dd[3]); 
            /*if($dd[3] == 'view' && isset($dd[4]) && $dd[4] == 'course') {
                if(isset($dd[6]) && !empty($dd[6])) {
                    $act_page = str_replace("_", " ", $dd[6]); 
                }   else {
                    $act_page = str_replace("_", " ", 'subjects'); 
                }
                /*   get list access for all the buttons -> packages, chapters, topics, docs, videos, tests   * /
                 
                foreach ($course_pages_access as $key => $value) { 
                    $key_page = ucwords($key); 
                    if (array_key_exists($key_page, $search)) {
                        $rights = $search[$key_page];
                        $list = $rights['list'];
                        $listclass = 'display:none';
                        if ($list == 1) {
                            $listclass = 'display:flex';
                        }  
                        $course_pages_access[$key]['list'] =  $list;
                    }
                } 
                /*  End * /   
            }  else */if($dd[3] == 'users') {
                $act_page = str_replace("_", " ", 'App Users'); 
            }    else {
                $act_page = str_replace("_", " ", $dd[3]); 
            }
            $active_page = $act_page;
            //$active_page = ucwords($act_page); 
               
            
            $rights = array();
            $display = '';
            $add = 0;
            $view = 0;
            $edit = 0;
            $delete = 0;
            $list = 0;
            $status_update = 0;
            $aadhar_status_update = 0;
            $addclass = 'display:none';
            $editclass = 'display:none';
            $viewclass = 'display:none';
            $deleteclass = 'display:none';
            $listclass = 'display:none';
            $statusupdateclass = 'display:none';
            $aadharstatusupdateclass = 'display:none';
            $rights = ['add'=>0, 'view'=>0, 'edit'=>0, 'delete'=>0, 'list'=>0, 'status_update'=>0, 'aadhar_status_update'=>0];
            if (array_key_exists($active_page, $search)) {
                $rights = $search[$active_page];
                
                $add = $rights['add'];
                $view = $rights['view'];
                $edit = $rights['edit'];
                $delete = $rights['delete'];
                $list = $rights['list'];
                $status_update = $rights['status_update'];
                $aadhar_status_update = $rights['aadhar_status_update'];
                if ($add == 1) {
                    $addclass = 'display:flex';
                }
                if ($edit == 1) {
                    $editclass = 'display:flex';
                }
                if ($view == 1) {
                    $viewclass = 'display:flex';
                }
                if ($delete == 1) {
                    $deleteclass = 'display:flex';
                }
                if ($list == 1) {
                    $listclass = 'display:flex';
                }
                if ($status_update == 1) {
                    $statusupdateclass = 'display:flex';
                } 
                
            }    
        }

        $user_role = session()->get('user_type');  
        if($user_role == 'SUPERADMIN') {
            $addclass = 'display:flex';
            $editclass = 'display:flex';
            $viewclass = 'display:flex';
            $deleteclass = 'display:flex';
            $listclass = 'display:flex';
            $statusupdateclass = 'display:flex';

            $rights = ['add'=>1, 'view'=>1, 'edit'=>1, 'delete'=>1, 'list'=>1, 'status_update'=>1, 'aadhar_status_update'=>1];

            $course_pages_access = ['subjects'=>['list'=>1], 'packages'=>['list'=>1], 'chapters'=>['list'=>1], 'topics'=>['list'=>1], 
            'docs'=>['list'=>1], 'videos'=>['list'=>1], 'tests'=>['list'=>1]];
        } 
        return array('addclass'=>$addclass, 'editclass'=>$editclass, 'viewclass'=>$viewclass, 'deleteclass'=>$deleteclass, 'listclass'=>$listclass, 'statusupdateclass'=>$statusupdateclass, 'rights'=>$rights, 'course_pages_access'=>$course_pages_access);
    }

    //  Excel Import
    /* Function: excelimport
     */
    public function excelimport()   {
        if(Auth::check()){
            
            return view('admin.excelimport');
        }else{
            return redirect('/admin/login');
        }
    }

    /*  Excel Sheet Uploaded */
     public function saveExcelImport(Request $request){
        $input = $request->all();
 
        $excel_file = $request->file('excel_file');


        $validator = Validator::make(
            [
               'excel_file'      => 'required',
               'extension' => strtolower($excel_file->getClientOriginalExtension()),
            ],
            [
               'excel_file'          => 'required',
               'extension'      => 'required|in:csv,xlsx,xls',

            ]
        );

        if ($validator->fails()) {

           $msg = "Invalid File Format. csv,xlsx,xls formats are only Allowed";

            return response()->json([

                'status'=>"FAILED",
                'data'=>$msg

           ]);

        }

        $path = $excel_file->getRealPath();
         
        $data = Excel::load($path, function($reader)  {
 

            $keys = [];
            foreach ($reader->toArray() as $row)   {
                $row1 = $row; 
                if(count($row1)>0) { // && isset($row1[0]) && is_array($row1[0])
                    $keys = array_keys($row1);
                    unset($keys['0']);
                }                  
                echo "<pre>";  print_r($keys);  print_r($languages_arr);print_r($row1); exit;
                $language_keyword_for = $row['for'];
                //foreach ($row1 as $row)   { 
                    $lang_key = trim($row['key']);
                    $lang_key = strtolower($lang_key);
                    if(!empty($lang_key)) {
                        $exists_key = DB::table('bk_language_keywords')->where('lang_keyword', $lang_key)
                            ->where('language_keyword_for', $language_keyword_for)->get();
                        if($exists_key->isNotEmpty()) {
                            $key_id = $exists_key[0]->id;
                        }
                        else {
                            $key_id = DB::table('bk_language_keywords')
                                ->insertGetId([
                                    'language_keyword_for' =>$language_keyword_for,
                                    'lang_keyword' => $lang_key, 
                                    'status' => 'ACTIVE',
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);
                        }
                        //echo "<pre>"; print_r($keys);print_r($languages_arr); exit;
                        foreach ($keys as $keys_lang)   {
                            if(isset($languages_arr[$keys_lang])) {
                                $keys_lang = strtolower($keys_lang);
                                $exists_key_lang = DB::table('bk_language_word_conversions')
                                    ->where('keyword_id', $key_id)->where('language_id', $languages_arr[$keys_lang])->get();
                                if($exists_key_lang->isNotEmpty()) {
                                     DB::table('bk_language_word_conversions')
                                        ->where('keyword_id', $key_id)
                                        ->where('language_id', $languages_arr[$keys_lang])
                                        ->update([
                                            'language_word' => $row[$keys_lang],
                                            'updated_at' => date('Y-m-d H:i:s')
                                        ]);
                                }
                                else {
                                    DB::table('bk_language_word_conversions')
                                        ->insert([
                                            'keyword_id' => $key_id, 
                                            'language_id' => $languages_arr[$keys_lang],
                                            'language_word' => $row[$keys_lang],
                                            'created_at' => date('Y-m-d H:i:s')
                                        ]);
                                }
                            }                            
                        }
                    }
                //}
            }

            // Update users for notifications to refresh in app
            DB::table('users')->update(['language_notify'=>'1']);

        });

        return response()->json(['status' => 'SUCCESS', 'message' => 'Excel Details Updated']);

    }

    // Grocery Vendors

    public function vendorPage()
    {
        if(Auth::check()){
        return view('admin.vendors');
        }else{

            return redirect('/admin/login');

        }

    }

    public function getVendors(Request $request)
    {

        /*$vendors = User::where('user_type','VENDOR');

        return Datatables::of($vendors)->make(true);*/

        $input = $request->all();
        $start = $input['start'];
        $length = $input['length'];

        $input = $request->all(); 
        $columns = $request->get('columns'); 
    
        $vendorsqry = User::where('user_type','VENDOR');
        
        if (count($columns) > 0) {
            foreach ($columns as $key => $value) {
                if (! empty($value['name']) && ! empty($value['search']['value'])) {
                    $vendorsqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                }
            }
        }
        $vendors = $vendorsqry->skip($start)->take($length)->get();

        $totalData = User::where('user_type','VENDOR')->count();  

        $totalFiltered = count($vendors); 
        
        $data = [];
        if(!empty($vendors))    {
            foreach ($vendors as $post)   {  
                $data[] = $post;
            }
        }
        

         $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 

    }

    public function getVendorsExcel(Request $request){

        if(Auth::check()){
            $vendors = User::where('user_type','VENDOR')->get()->toArray();

            $dataitem = [];
            if(!empty($vendors)) {  
                foreach($vendors as $rev) {   
                    $dataitem[] = ["User Name" => $rev['username'], 
                                   "Mobile" => $rev['mobile'],  
                                   "Email" => $rev['email'], 
                                   "Status" => $rev['user_status']
                               ];
                }
            }

             header("Content-Type: text/plain");

              $flag = false;
              foreach($dataitem as $row) {
                if(!$flag) {
                  // display field/column names as first row
                  echo implode("\t", array_keys($row)) . "\r\n";
                  $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
              }
              exit;

        
        }else{
            return redirect('/admin/login');
        }

    }

    public function viewVendor($id,$code){
        if(Auth::check()){

        $user = User::find($id);

        $vendor = Vendor::where('user_id',$id)->first(); 

        return view('admin.view_vendor')->with('user',$user)->with('vendor',$vendor);
        }else{

            return redirect('/admin/login');

        }

    }

    public function editVendor($id,$code){
        if(Auth::check()){

        $user = User::find($id);
        $warehouses = User::where('user_type','WAREHOUSE')->where('status', 'ACTIVE')
            ->select('users.id', 'users.name as warehouse_name')->get(); 
        $vendor = Vendor::where('user_id',$id)->first(); 
        $zones = DB::table('em_zones')->where('status','ACTIVE')->get();
        return view('admin.edit-vendor')->with('user',$user)->with('vendor',$vendor)
            ->with('zones',$zones)
            ->with('warehouses',$warehouses);
        }else{
            return redirect('/admin/login');
        }

    }

    public function putVendor(Request $request){
        if(Auth::check()){
        $userid = $request->user_id;
        $vendorid = $request->vendor_id;
        $username = $request->vendor_name;
        $mobile = $request->vendor_mobile;
        $email = $request->email;

        $bankdetails = $request->bank_details;
        $identityno = $request->identify_info;
        $expire_date = $request->expire_date;
        $firstname = $request->first_name;
        $lastname =  $request->last_name;
        $dob  = $request->dob;
        $address = $request->residental_address;
        $businessname = $request->business_name;
        $businesscontactno = $request->business_contact_no;
        $businessaddress = $request->business_address;
        $productinfo = $request->product_info;
        $nationalid = $request->file('national_id');
        $bankst = $request->file('bank_st');
        $otherdoc = $request->file('other_doc');
        $licenseno = $request->file('license_no');
        $vendor_logo = $request->file('vendor_logo');
        $gstcompname = $request->gst_comp_name;
        $gstno = $request->gst_no;
        $status = $request->status;
        $commission_amount = $request->commission_amount;

        $mapped_zones = $request->mapped_zones;
        $mapped_warehouses = $request->mapped_warehouses;

        $country = $request->issue_country;

        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required',
            'vendor_mobile' => 'required',
            'email' => 'required',
            'bank_details' => 'required',
            'identify_info' => 'required',
            'expire_date' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required',
            'residental_address' => 'required',
            'business_name' => 'required',
            'business_contact_no' => 'required',
            'business_address' => 'required',
            'product_info' => 'required',
            'issue_country'=>'required',
            'gst_comp_name'=>'required',
            'gst_no'=>'required',
            'commission_amount'=>'required',
            'mapped_zones'=>'required',
            'mapped_warehouses'=>'required',
            //'vendor_logo'=>'required',
        ],
        [
            'vendor_name.required' => 'Please enter Vendor Name',
            'vendor_mobile.required' => 'Please enter Vendor Mobile',
            'email.required' => 'Please enter Vendor Email',
            'bank_details.required' => 'Please enter Account Information',
            'identify_info.required' => 'Please enter Identify Information',
            'expire_date.required' => 'Please enter Expire Date',
            'first_name.required' => 'Please enter First Name',
            'last_name.required' => 'Please enter Last Name',
            'dob.required' => 'Please enter Vendor DOB',
            'residental_address.required' => 'Please enter Residental Address',
            'business_name.required' => 'Please enter Business Name',
            'business_contact_no.required' => 'Please enter Business Contact No.',
            'business_address.required' => 'Please enter Business Address',
            'issue_country.required'=>'Please enter Country of issue', 
            'product_info.required'=> 'Please enter Product Information',
            'gst_comp_name.required'=>'Please enter the GST Name',
            'gst_no.required'=>'Please enter the GST Number',
            'commission_amount.required'=>'Please enter the Commmission Amount',
            'mapped_zones.required'=>'Please Choose the Mapped Zone',
            'mapped_warehouses.required'=>'Please Choose the Mapped Warehouse',
            //'vendor_logo.required'=>'Please Upload the Logo',
        ]);

        if ($validator->fails()) {

            $msgerr = '';
            $msg = $validator->errors()->all();
            if(is_array($msg) && count($msg)>0){
                $msgerr = implode(' ', $msg);
            }
            return response()->json([

                'status' => "FAILED",
                'message' => "Invalid Inputs".$msgerr,
                'data' => $msg

            ]);
        }

        $user = User::find($userid);

        $user->name = $username;

        $user->email =  $email;

        $user->mobile = $mobile;

        $user->user_type = 'VENDOR';

        if(!empty($request->password)) {
            $password = Hash::make($request->password);
            $user->password = $password;
        }        

        $user->status = $status;

        $user->save();


        $vendor = Vendor::find($vendorid);

        $vendor->code = time();

        $vendor->user_id = $user->id;

        $vendor->company_name = $businessname;

        $vendor->mobile = $businesscontactno;

        $vendor->business_address = $businessaddress;

        $vendor->first_name = $firstname;

        $vendor->last_name = $lastname;

        $vendor->dob = $dob;

        $vendor->residental_address = $address;

        $vendor->expire_date = $expire_date;

        $vendor->bank_details =  $bankdetails;

        $vendor->identity_no =  $identityno;

        $vendor->product_info = $productinfo;

        $vendor->status = $status;

        $vendor->country =  $country;

        $vendor->commission_amount =  $commission_amount;

        $vendor->joined_date =  date('Y-m-d');

        $vendor->gst_comp_name = $gstcompname;

        $vendor->gst_no = $gstno;

        $mapped_zones_str = '';
        if(is_array($mapped_zones) && count($mapped_zones)>0) {
            $mapped_zones_str = implode(',', $mapped_zones);
        }

        $vendor->mapped_zones = $mapped_zones_str;  

        $mapped_warehouses_str = '';
        if(is_array($mapped_warehouses) && count($mapped_warehouses)>0) {
            $mapped_warehouses_str = implode(',', $mapped_warehouses);
        }

        $vendor->mapped_warehouses = $mapped_warehouses_str;

        if (!empty($nationalid)) {


            $nationidName = rand().time() . '.' . $nationalid->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $nationalid->move($destinationPath, $nationidName);

            $vendor->national_id = 'uploads/documents/' . $nationidName;

        }


        if (!empty($bankst)) {


            $bankName = rand().time() . '.' . $bankst->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $bankst->move($destinationPath, $bankName);

            $vendor->bank_st = 'uploads/documents/' . $bankName;

        }


        if (!empty($otherdoc)) {


            $otherdocname = rand().time() . '.' . $otherdoc->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $otherdoc->move($destinationPath, $otherdocname);

            $vendor->other_doc = 'uploads/documents/' . $otherdocname;

        }


        if (!empty($licenseno)) {


            $licname = rand().time() . '.' . $licenseno->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $licenseno->move($destinationPath, $licname);

            $vendor->license_no = 'uploads/documents/' . $licname;

        }

        if (!empty($vendor_logo)) {


            $otherdocname = rand().time() . '.' . $vendor_logo->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $vendor_logo->move($destinationPath, $otherdocname);

            $vendor->vendor_logo = 'uploads/documents/' . $otherdocname;

        }

        $vendor->save();

        // DB::table('vendor_prefer_categories')->where('vendor_id',$vendor->id)->delete();


        // $category = $request->product_category;

        // for($i =  0; $i<sizeof($category); $i++){

        //     DB::table('vendor_prefer_categories')->insert(
        //         [
        //             'vendor_id'=>$vendor->id,
        //             'category_id'=>$category[$i]
        //         ]);

        // }


        if(!empty($vendor)){

            return response()->json(['status' => 'SUCCESS', 'message' => 'Vendor Updated']);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
        }
        }else{

            return redirect('/admin/login');

        }

    }

    public function viewAddVendor(){
        if(Auth::check()){
        $warehouses = User::where('user_type','WAREHOUSE')->where('status', 'ACTIVE')
            ->select('users.id', 'users.name as warehouse_name')->get();
        $mastercategories = DB::table('master_categories')->where('is_display','YES')->get();
        $zones = DB::table('em_zones')->where('status','ACTIVE')->get();
        return view('admin.add-vendor')->with('categories',$mastercategories)->with('zones',$zones)->with('warehouses',$warehouses);
        }else{

            return redirect('/admin/login');

        }

    }

    public function postVendor(Request $request){

        if(Auth::check()){
        $username = $request->vendor_name;
        $mobile = $request->vendor_mobile;
        $email = $request->email;
        $password = Hash::make($request->password);
        $bankdetails = $request->bank_details;
        $identityno = $request->identify_info;
        $expire_date = $request->expire_date;
        $firstname = $request->first_name;
        $lastname =  $request->last_name;
        $status = 'ACTIVE';
        $dob  = $request->dob;
        $address = $request->residental_address;
        $businessname = $request->business_name;
        $businesscontactno = $request->business_contact_no;
        $businessaddress = $request->business_address;
        $productinfo = $request->product_info;
        $nationalid = $request->file('national_id');
        $bankst = $request->file('bank_st');
        $otherdoc = $request->file('other_doc');
        $licenseno = $request->file('license_no');
        $vendor_logo = $request->file('vendor_logo');
        $gstcompname = $request->gst_comp_name;
        $gstno = $request->gst_no;

        $country = $request->issue_country;
        $commission_amount = $request->commission_amount;
        $mapped_zones = $request->mapped_zones;
        $mapped_warehouses = $request->mapped_warehouses;

        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required',
            'vendor_mobile' => 'required',
            'email' => 'required',
            'password' => 'required',
            'bank_details' => 'required',
            'identify_info' => 'required',
            'expire_date' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required',
            'residental_address' => 'required',
            'business_name' => 'required',
            'business_contact_no' => 'required',
            'business_address' => 'required',
            'product_info' => 'required',
            'issue_country'=>'required',
            'gst_comp_name'=> 'required',
            'gst_no'=> 'required',
            'commission_amount'=>'required',
            'mapped_zones'=>'required',
            'mapped_warehouses'=>'required',
            'vendor_logo'=>'required',
        ],
        [
            'vendor_name.required' => 'Please enter Vendor Name',
            'vendor_mobile.required' => 'Please enter Vendor Mobile',
            'email.required' => 'Please enter Vendor Email',
            'password.required' => 'Please enter Vendor Password',
            'bank_details.required' => 'Please enter Account Information',
            'identify_info.required' => 'Please enter Identify Information',
            'expire_date.required' => 'Please enter Expire Date',
            'first_name.required' => 'Please enter First Name',
            'last_name.required' => 'Please enter Last Name',
            'dob.required' => 'Please enter Vendor DOB',
            'residental_address.required' => 'Please enter Residental Address',
            'business_name.required' => 'Please enter Business Name',
            'business_contact_no.required' => 'Please enter Business Contact No.',
            'business_address.required' => 'Please enter Business Address',
            'issue_country.required'=>'Please enter Country of issue', 
            'product_info.required'=> 'Please enter Product Information',
            'gst_comp_name.required'=>'Please enter the GST Name',
            'gst_no.required'=>'Please enter the GST Number',
            'commission_amount.required'=>'Please enter the Commmission Amount',
            'mapped_zones.required'=>'Please Choose the Mapped Zone',
            'mapped_warehouses.required'=>'Please Choose the Mapped Warehouse',
            'vendor_logo.required'=>'Please Upload the Logo',
        ]);

        if ($validator->fails()) {

            $msgerr = '';
            $msg = $validator->errors()->all();
            if(is_array($msg) && count($msg)>0){
                $msgerr = implode(' ', $msg);
            }
            return response()->json([

                'status' => "FAILED",
                'message' => "Invalid Inputs".$msgerr,
                'data' => $msg

            ]);
        }

        $user = new User;

        $today = date('ymd');
        $fircheck_qry = "SELECT reg_no FROM users WHERE reg_no LIKE '$today%' ORDER BY id DESC LIMIT 1";
        $fircheck = DB::select($fircheck_qry); 
        if(is_array($fircheck) && count($fircheck) > 0) {
            $reg_no = $fircheck[0]->reg_no;
            $user_reg_no = $reg_no + 1;
        }   else {
            $user_reg_no = $today . '0001';
        } 

        $user->name = $username;

        $user->reg_no = $user_reg_no;

        $user->email =  $email;

        $user->mobile = $mobile;

        $user->password = $password;

        $country_id = 1;
        $user->country = $country_id;
        $country_code = Countries::where('status', 'ACTIVE')->where('id', $country_id)->value('phonecode');
        $user->country_code = $country_code;
        $user->code_mobile = $country_code.$mobile;

        $user->user_type = 'VENDOR';

        $user->status = $status; 

        $referral_code = User::random_strings(5);
        $user->referal_code = $referral_code;
        $user->joined_date = date('Y-m-d H:i:s');

        $device_type = $device_id = 'ADMIN';
        $fcm_id = '';

        $date = date('Y-m-d H:i:s');
        $user->last_login_date = $date;
        $user->last_app_opened_date = $date;
        $user->user_source_from = $device_type;
        $user->api_token = User::random_strings(30);

        $def_expiry_after =  CommonController::getDefExpiry();

        $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
        $user->created_at = $date;
        $user->referral_code = User::random_strings(5);
        $user->wallet_amount = 0;
        $user->gender = 'MALE';
        $user->joined_date =  date('Y-m-d');

        $user->save();
 

        DB::table('em_users_loginstatus')->insert([
            'user_id' => $user->id,
            'fcm_id' => $fcm_id,
            'device_id' => $device_id,
            'device_type' => $device_type,
            'api_token_expiry' => $user->api_token_expiry,
            'status' => 'LOGIN',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        DB::table('users_active_status')->insert([
            'user_id' => $user->id,
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);             

        $vendor = new Vendor;

        $today = date('ymd');
        $fircheck_qry = "SELECT code as reg_no FROM vendors WHERE code LIKE '$today%' ORDER BY id DESC LIMIT 1";
        $fircheck = DB::select($fircheck_qry); 
        if(is_array($fircheck) && count($fircheck) > 0) {
            $reg_no = $fircheck[0]->reg_no;
            $user_reg_no = $reg_no + 1;
        }   else {
            $user_reg_no = $today . '0001';
        } 

        $vendor->code = $user_reg_no;

        $vendor->user_id = $user->id;

        $vendor->company_name = $businessname;

        $vendor->mobile = $businesscontactno;

        $vendor->business_address = $businessaddress;

        $vendor->first_name = $firstname;

        $vendor->last_name = $lastname;

        $vendor->dob = $dob;

        $vendor->residental_address = $address;

        $vendor->expire_date = $expire_date;

        $vendor->bank_details =  $bankdetails;

        $vendor->identity_no =  $identityno;

        $vendor->product_info = $productinfo;

        $vendor->status = $status;

        $vendor->country =  $country;

        $vendor->commission_amount =  $commission_amount;

        $vendor->joined_date =  date('Y-m-d');

        $vendor->gst_no = $gstno;

        $vendor->gst_comp_name = $gstcompname;

        $mapped_zones_str = '';
        if(is_array($mapped_zones) && count($mapped_zones)>0) {
            $mapped_zones_str = implode(',', $mapped_zones);
        }

        $vendor->mapped_zones = $mapped_zones_str;

        $mapped_warehouses_str = '';
        if(is_array($mapped_warehouses) && count($mapped_warehouses)>0) {
            $mapped_warehouses_str = implode(',', $mapped_warehouses);
        }

        $vendor->mapped_warehouses = $mapped_warehouses_str;
        
        if (!empty($nationalid)) {


            $nationidName = rand().time() . '.' . $nationalid->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $nationalid->move($destinationPath, $nationidName);

            $vendor->national_id = 'uploads/documents/' . $nationidName;

        }


        if (!empty($bankst)) {


            $bankName = rand().time() . '.' . $bankst->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $bankst->move($destinationPath, $bankName);

            $vendor->bank_st = 'uploads/documents/' . $bankName;

        }


        if (!empty($otherdoc)) {


            $otherdocname = rand().time() . '.' . $otherdoc->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $otherdoc->move($destinationPath, $otherdocname);

            $vendor->other_doc = 'uploads/documents/' . $otherdocname;

        }

        if (!empty($licenseno)) {


            $licname = rand().time() . '.' . $licenseno->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $licenseno->move($destinationPath, $licname);

            $vendor->license_no = 'uploads/documents/' . $licname;

        }

        if (!empty($vendor_logo)) {


            $otherdocname = rand().time() . '.' . $vendor_logo->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $vendor_logo->move($destinationPath, $otherdocname);

            $vendor->vendor_logo = 'uploads/documents/' . $otherdocname;

        }

        $vendor->save();  

        if(!empty($vendor)){

            return response()->json(['status' => 'SUCCESS', 'message' => 'Vendor Added']);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
        }
        }else{

            return redirect('/admin/login');

        }

    }

    // WareHouses

    public function WarehousePage()
    {
        if(Auth::check()){
        return view('admin.warehouse');
        }else{

            return redirect('/admin/login');

        }

    }

    public function getWarehouse(Request $request)
    {

        /*$vendors = User::where('user_type','VENDOR');

        return Datatables::of($vendors)->make(true);*/

        $input = $request->all();
        $start = $input['start'];
        $length = $input['length'];

        $input = $request->all(); 
        $columns = $request->get('columns'); 
    
        $vendorsqry = User::where('user_type','WAREHOUSE');
        
        if (count($columns) > 0) {
            foreach ($columns as $key => $value) {
                if (! empty($value['name']) && ! empty($value['search']['value'])) {
                    $vendorsqry->where($value['name'], 'like', '%' . $value['search']['value'] . '%');
                }
            }
        }
        $vendors = $vendorsqry->skip($start)->take($length)->get();

        $totalData = User::where('user_type','WAREHOUSE')->count();  

        $totalFiltered = count($vendors); 
        
        $data = [];
        if(!empty($vendors))    {
            foreach ($vendors as $post)   {  
                $data[] = $post;
            }
        }
        

         $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 

    }

    public function getWarehouseExcel(Request $request){

        if(Auth::check()){
            $vendors = User::where('user_type','WAREHOUSE')->get()->toArray();

            $dataitem = [];
            if(!empty($vendors)) {  
                foreach($vendors as $rev) {   
                    $dataitem[] = ["User Name" => $rev['username'], 
                                   "Mobile" => $rev['mobile'],  
                                   "Email" => $rev['email'], 
                                   "Status" => $rev['user_status']
                               ];
                }
            }

             header("Content-Type: text/plain");

              $flag = false;
              foreach($dataitem as $row) {
                if(!$flag) {
                  // display field/column names as first row
                  echo implode("\t", array_keys($row)) . "\r\n";
                  $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
              }
              exit;

        
        }else{
            return redirect('/admin/login');
        }

    }

    public function viewWarehouse($id,$code){
        if(Auth::check()){

        $user = User::find($id);

        $warehouse = Warehouse::where('user_id',$id)->first(); 

        return view('admin.view_warehouse')->with('user',$user)->with('warehouse',$warehouse);
        }else{

            return redirect('/admin/login');

        }

    }

    public function editWarehouse($id,$code){
        if(Auth::check()){

            $user = User::find($id);
            $mastercategories = DB::table('master_categories')->where('is_display','YES')->get();
            $warehouse = Warehouse::where('user_id',$id)->first(); 
            $chsnzones = DB::table('users')
                ->leftjoin('warehouse', 'warehouse.user_id', 'users.id')
                ->where('user_type', 'WAREHOUSE')
                ->where('users.status', 'ACTIVE')
                ->where('user_id', '!=', $id)
                ->select('mapped_zones')->get();
            $zones = [];
            if($chsnzones->isNotEmpty()) {
                foreach($chsnzones as $zone) {
                    $zones[] = $zone->mapped_zones;
                } 
            }    
            $zones = DB::table('em_zones')->whereNotIn('id',$zones)->where('status','ACTIVE')->get();
            
        return view('admin.edit-warehouse')->with('user',$user)->with('warehouse',$warehouse)->with('zones',$zones)->with('categories',$mastercategories);
        }else{
            return redirect('/admin/login');
        }

    }

    public function putWarehouse(Request $request){
        if(Auth::check()){
        $userid = $request->user_id;
        $vendorid = $request->vendor_id;
        $username = $request->vendor_name;
        $mobile = $request->vendor_mobile;
        $email = $request->email;

        $bankdetails = $request->bank_details;
        $identityno = $request->identify_info;
        $expire_date = $request->expire_date;
        $firstname = $request->first_name;
        $lastname =  $request->last_name;
        $dob  = $request->dob;
        $address = $request->residental_address;
        $businessname = $request->business_name;
        $businesscontactno = $request->business_contact_no;
        $businessaddress = $request->business_address; 
        $nationalid = $request->file('national_id');
        $bankst = $request->file('bank_st');
        $otherdoc = $request->file('other_doc');
        $licenseno = $request->file('license_no');
        $gstcompname = $request->gst_comp_name;
        $gstno = $request->gst_no;
        $status = $request->status;
        $commission_amount = $request->commission_amount;

        $mapped_zones = $request->mapped_zones;

        $country = $request->issue_country;

        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required',
            'vendor_mobile' => 'required',
            'email' => 'required',   
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required',
            'residental_address' => 'required',
            'business_name' => 'required',
            'business_contact_no' => 'required',
            'business_address' => 'required', 
            'gst_comp_name'=>'required',
            'gst_no'=>'required',
            'commission_amount'=>'required',
            'mapped_zones'=>'required',
            'mapped_categories'=>'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Invalid Inputs"

            ]);
        }

        $mobileEx = DB::table('users')->where('mobile', $mobile)->where('user_type', 'WAREHOUSE');
        if ($userid > 0) {
            $mobileEx->where('id', '!=', $userid);
        } 
        $mobileEx = $mobileEx->first();

        if(!empty($mobileEx)) {
            return response()->json([
                'status' => "FAILED",
                'message' => "Mobile Number already exists"
            ]);
        }

        $mobileEx = DB::table('users')->where('email', $email)->where('user_type', 'WAREHOUSE');
        if ($userid > 0) {
            $mobileEx->where('id', '!=', $userid);
        } 
        $mobileEx = $mobileEx->first();

        if(!empty($mobileEx)) {
            return response()->json([
                'status' => "FAILED",
                'message' => "Email already exists"
            ]);
        }

        $user = User::find($userid);

        $user->name = $username;

        $user->email =  $email;

        $user->mobile = $mobile;

        $user->user_type = 'WAREHOUSE';

        if(!empty($request->password)) {
            $password = Hash::make($request->password);
            $user->password = $password;
        }        

        $user->status = $status;

        $user->save();


        $vendor = Warehouse::find($vendorid);

        $vendor->code = time();

        $vendor->user_id = $user->id;

        $vendor->company_name = $businessname;

        $vendor->mobile = $businesscontactno;

        $vendor->business_address = $businessaddress;

        $vendor->first_name = $firstname;

        $vendor->last_name = $lastname;

        $vendor->dob = $dob;

        $vendor->residental_address = $address; 

        $vendor->status = $status;

        $vendor->country =  $country;

        $vendor->commission_amount =  $commission_amount;

        $vendor->joined_date =  date('Y-m-d');

        $vendor->gst_comp_name = $gstcompname;

        $vendor->gst_no = $gstno;

        $mapped_zones_str = '';
        if(is_array($mapped_zones) && count($mapped_zones)>0) {
            $mapped_zones_str = implode(',', $mapped_zones);
        }

        $vendor->mapped_zones = $mapped_zones_str;

        if (!empty($nationalid)) {


            $nationidName = rand().time() . '.' . $nationalid->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $nationalid->move($destinationPath, $nationidName);

            $vendor->national_id = 'uploads/documents/' . $nationidName;

        }


        if (!empty($bankst)) {


            $bankName = rand().time() . '.' . $bankst->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $bankst->move($destinationPath, $bankName);

            $vendor->bank_st = 'uploads/documents/' . $bankName;

        }


        if (!empty($otherdoc)) {


            $otherdocname = rand().time() . '.' . $otherdoc->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $otherdoc->move($destinationPath, $otherdocname);

            $vendor->other_doc = 'uploads/documents/' . $otherdocname;

        }


        if (!empty($licenseno)) {


            $licname = rand().time() . '.' . $licenseno->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $licenseno->move($destinationPath, $licname);

            $vendor->license_no = 'uploads/documents/' . $licname;

        }

        $vendor->save();

        // DB::table('vendor_prefer_categories')->where('vendor_id',$vendor->id)->delete();


        // $category = $request->product_category;

        // for($i =  0; $i<sizeof($category); $i++){

        //     DB::table('vendor_prefer_categories')->insert(
        //         [
        //             'vendor_id'=>$vendor->id,
        //             'category_id'=>$category[$i]
        //         ]);

        // }


        if(!empty($vendor)){

            return response()->json(['status' => 'SUCCESS', 'message' => 'Vendor Updated']);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
        }
        }else{

            return redirect('/admin/login');

        }

    }

    public function viewAddWarehouse(){
        if(Auth::check()){
            $categories = DB::table('main_categories')->where('status','ACTIVE')->get();
            $chsnzones = DB::table('users')
                ->leftjoin('warehouse', 'warehouse.user_id', 'users.id')
                ->where('user_type', 'WAREHOUSE')
                ->where('users.status', 'ACTIVE')->select('mapped_zones')->get();
            $zones = [];
            if($chsnzones->isNotEmpty()) {
                foreach($chsnzones as $zone) {
                    $zones[] = $zone->mapped_zones;
                } 
            }    
            $zones = DB::table('em_zones')->whereNotIn('id',$zones)->where('status','ACTIVE')->get();
            return view('admin.add-warehouse')->with('categories',$categories)->with('zones',$zones);
        }else{

            return redirect('/admin/login');

        }

    }

    public function postWarehouse(Request $request){

        if(Auth::check()){
        $username = $request->vendor_name;
        $mobile = $request->vendor_mobile;
        $email = $request->email;
        $password = Hash::make($request->password); 
        $identityno = $request->identify_info; 
        $firstname = $request->first_name;
        $lastname =  $request->last_name;
        $status = 'ACTIVE';
        $dob  = $request->dob;
        $address = $request->residental_address;
        $businessname = $request->business_name;
        $businesscontactno = $request->business_contact_no;
        $businessaddress = $request->business_address; 
        $identity_proof = $request->file('identity_proof'); 
        $otherdoc = $request->file('other_doc'); 
        $gstcompname = $request->gst_comp_name;
        $gstno = $request->gst_no; 
        $commission_amount = $request->commission_amount;
        $mapped_zones = $request->mapped_zones;
        $mapped_categories = $request->mapped_categories;
        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required',
            'vendor_mobile' => 'required',
            'email' => 'required',
            'password' => 'required', 
            'identify_info' => 'required', 
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required',
            'residental_address' => 'required',
            'business_name' => 'required',
            'business_contact_no' => 'required',
            'business_address' => 'required', 
            'gst_comp_name'=> 'required',
            'gst_no'=> 'required',
            'commission_amount'=>'required',
            'mapped_zones'=>'required',
            'mapped_categories' => 'required',
        ]);

        if ($validator->fails()) {

            $msg = $validator->errors()->all();

            return response()->json([

                'status' => "FAILED",
                'message' => "Invalid Inputs"

            ]);
        }

        $user = new User;

        $today = date('ymd');
        $fircheck_qry = "SELECT reg_no FROM users WHERE reg_no LIKE '$today%' ORDER BY id DESC LIMIT 1";
        $fircheck = DB::select($fircheck_qry); 
        if(is_array($fircheck) && count($fircheck) > 0) {
            $reg_no = $fircheck[0]->reg_no;
            $user_reg_no = $reg_no + 1;
        }   else {
            $user_reg_no = $today . '0001';
        } 

        $mobileEx = DB::table('users')->where('mobile', $mobile)->where('user_type', 'WAREHOUSE')->first();

        if(!empty($mobileEx)) {
            return response()->json([
                'status' => "FAILED",
                'message' => "Mobile Number already exists"
            ]);
        }

        $mobileEx = DB::table('users')->where('email', $email)->where('user_type', 'WAREHOUSE')->first();

        if(!empty($mobileEx)) {
            return response()->json([
                'status' => "FAILED",
                'message' => "Email already exists"
            ]);
        }

        $user->name = $username;

        $user->reg_no = $user_reg_no;

        $user->email =  $email;

        $user->mobile = $mobile;

        $user->password = $password;

        $country_id = 1;
        $user->country = $country_id;
        $country_code = Countries::where('status', 'ACTIVE')->where('id', $country_id)->value('phonecode');
        $user->country_code = $country_code;
        $user->code_mobile = $country_code.$mobile;

        $user->user_type = 'WAREHOUSE';

        $user->status = $status; 

        $referral_code = User::random_strings(5);
        $user->referal_code = $referral_code;
        $user->joined_date = date('Y-m-d H:i:s');

        $device_type = $device_id = 'ADMIN';
        $fcm_id = '';

        $date = date('Y-m-d H:i:s');
        $user->last_login_date = $date;
        $user->last_app_opened_date = $date;
        $user->user_source_from = $device_type;
        $user->api_token = User::random_strings(30);

        $def_expiry_after =  CommonController::getDefExpiry();

        $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
        $user->created_at = $date;
        $user->referral_code = User::random_strings(5);
        $user->wallet_amount = 0;
        $user->gender = 'MALE';
        $user->joined_date =  date('Y-m-d');

        $user->save();
 

        DB::table('em_users_loginstatus')->insert([
            'user_id' => $user->id,
            'fcm_id' => $fcm_id,
            'device_id' => $device_id,
            'device_type' => $device_type,
            'api_token_expiry' => $user->api_token_expiry,
            'status' => 'LOGIN',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        DB::table('users_active_status')->insert([
            'user_id' => $user->id,
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);             

        $vendor = new Warehouse;

        $today = date('ymd');
        $fircheck_qry = "SELECT code as reg_no FROM warehouse WHERE code LIKE '$today%' ORDER BY id DESC LIMIT 1";
        $fircheck = DB::select($fircheck_qry); 
        if(is_array($fircheck) && count($fircheck) > 0) {
            $reg_no = $fircheck[0]->reg_no;
            $user_reg_no = $reg_no + 1;
        }   else {
            $user_reg_no = $today . '0001';
        } 

        $vendor->code = $user_reg_no;

        $vendor->user_id = $user->id;

        $vendor->company_name = $businessname;

        $vendor->mobile = $businesscontactno;

        $vendor->business_address = $businessaddress;

        $vendor->first_name = $firstname;

        $vendor->last_name = $lastname;

        $vendor->dob = $dob;

        $vendor->residental_address = $address;  

        $vendor->status = $status; 

        $vendor->commission_amount =  $commission_amount;

        $vendor->joined_date =  date('Y-m-d');

        $vendor->gst_no = $gstno;

        $vendor->gst_comp_name = $gstcompname;

        $mapped_zones_str = '';
        if(is_array($mapped_zones) && count($mapped_zones)>0) {
            $mapped_zones_str = implode(',', $mapped_zones);
        }

        $vendor->mapped_zones = $mapped_zones_str;

        $mapped_categories_str = '';
        if(is_array($mapped_categories) && count($mapped_categories)>0) {
            $mapped_categories_str = implode(',', $mapped_categories);
        }

        $vendor->mapped_categories = $mapped_categories_str;
        
        if (!empty($identity_proof)) {


            $nationidName = rand().time() . '.' . $identity_proof->getClientOriginalExtension();

            $destinationPath = public_path('/uploads/documents');

            $identity_proof->move($destinationPath, $nationidName);

            $vendor->identity_proof = 'uploads/documents/' . $nationidName;

        } 

        $others = '';
        if (!empty($otherdoc)) {
            foreach($otherdoc as $other) {

                $otherdocname = rand().time() . '.' . $other->getClientOriginalExtension();

                $destinationPath = public_path('/uploads/documents');

                $other->move($destinationPath, $otherdocname);

                $others .= 'uploads/documents/' . $otherdocname.';';
                
            }
        } 
        $vendor->gallery = $others;
        $vendor->save();  

        if(!empty($vendor)){

            return response()->json(['status' => 'SUCCESS', 'message' => 'Warehouse Added']);

        }else{

            return response()->json(['status' => 'FAILED', 'message' => 'Something went to wrong']);
        }
        }else{

            return redirect('/admin/login');

        }

    }
 
}