<?php  use App\Countries; 
if(isset(Auth::User()->id))  { 
  } else {  header('Location: '.URL('/admin'));exit;?>
<?php }

$countries = Countries::where('status', 'ACTIVE')->orderby('position', 'asc')->get(); 
$session_country = Session::get('session_country');
?>

<?php
use App\User;
use App\Module;
use App\RoleModuleMapping;
  
$active_page = basename($_SERVER['REQUEST_URI']);
$current_page = '';
$session_module = session()->get('module');
$role_fk = session()->get('role_fk');
$user_role = session()->get('user_type');
$current_page_result = Module::where('url', $active_page)->first();

if (! empty($current_page)) {
    $current_page = $current_page_result->parent_module_fk;
}

//echo "<pre>"; print_r($session_module); exit; 
?>

<!-- fixed-top-->
  <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-static-top  navbar-brand-center">
    <div class="navbar-wrapper">
      <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
          <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu font-large-1"></i></a></li>
          <li class="nav-item">
            <a class="navbar-brand" href="{{URL('/')}}">
              <img class="brand-logo" alt='{{ config("constants.site_name") }} admin logo' src="{{asset('/public/image/logo.png')}}" height="40" width="40">
              <h2 class="brand-text">{{ config("constants.site_name") }}</h2>
            </a>
          </li>
          <li class="nav-item d-md-none">
            <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i class="fa fa-ellipsis-v"></i></a>
          </li>
        </ul>
      </div>
      <div class="navbar-container content">
        <div class="collapse navbar-collapse" id="navbar-mobile">
          <ul class="nav navbar-nav mr-auto float-left">
            <li class="nav-item d-none d-md-block"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu"></i></a></li>
          </ul>
          <ul class="nav navbar-nav float-right">
            <li class="dropdown dropdown-notification nav-item d-none">
              <a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon ft-bell"></i>
                <span class="badge badge-pill badge-default badge-danger badge-default badge-up">5</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                <li class="dropdown-menu-header">
                  <h6 class="dropdown-header m-0">
                    <span class="grey darken-2">Notifications</span>
                    <span class="notification-tag badge badge-default badge-danger float-right m-0">5 New</span>
                  </h6>
                </li>
                <li class="scrollable-container media-list">
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-plus-square icon-bg-circle bg-cyan"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">You have new order!</h6>
                        <p class="notification-text font-small-3 text-muted">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">30 minutes ago</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-download-cloud icon-bg-circle bg-red bg-darken-1"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading red darken-1">99% Server load</h6>
                        <p class="notification-text font-small-3 text-muted">Aliquam tincidunt mauris eu risus.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Five hour ago</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-alert-triangle icon-bg-circle bg-yellow bg-darken-3"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading yellow darken-3">Warning notifixation</h6>
                        <p class="notification-text font-small-3 text-muted">Vestibulum auctor dapibus neque.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Today</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-check-circle icon-bg-circle bg-cyan"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">Complete the task</h6>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last week</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-file icon-bg-circle bg-teal"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">Generate monthly report</h6>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last month</time>
                        </small>
                      </div>
                    </div>
                  </a>
                </li>
                <li class="dropdown-menu-footer"><a class="dropdown-item text-muted text-center" href="javascript:void(0)">Read all notifications</a></li>
              </ul>
            </li>
            <li class="nav-item">
              <meta name="csrf-token" content="{{ csrf_token() }}">
              <select class="form-control" name="session_country" id="session_country" onchange="setAdminCountry()" style="margin-top: 10%">
                @if(!empty($countries))
                  @foreach($countries as $country)
                    <option value="{{$country->id}}" @if($country->id == $session_country) selected @endif>{{$country->name}}</option>
                  @endforeach
                @else 
                <option value="1">India</option> 
                @endif
              </select>
            </li>
            <li class="dropdown dropdown-user nav-item">
              <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                <span class="avatar avatar-online">
                  <img src="{{asset('/adminstack30/stack-admin-3.0/app-assets/images/portrait/small/avatar-s-1.png')}}" alt="avatar"><i></i></span>
                <span class="user-name">{{Auth::User()->name}}</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#"><i class="ft-user"></i>{{Auth::User()->name}}</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{URL('/')}}/admin/logout"><i class="ft-power"></i> Logout</a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
  <!-- Horizontal navigation-->
  <div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-light navbar-without-dd-arrow navbar-shadow menu-border"
  role="navigation" data-menu="menu-wrapper">
    <!-- Horizontal menu content-->
    <div class="navbar-container main-menu-content" data-menu="menu-container">
      <!-- include ../../../includes/mixins-->
      <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
        @if($user_role == 'SUPERADMIN' || (isset($session_module['home'])))
        <li class="nav-item @yield('dashboard')" data-menu="dropdown">
          <a class=" nav-link" href="{{URL('/')}}/admin/home"><i class="ft-home"></i>
            <span>Dashboard</span>
          </a>
          
        </li>
        @endif
        @if($user_role == 'SUPERADMIN' || (isset($session_module['settings']))  || (isset($session_module['about'])) || (isset($session_module['terms'])) || (isset($session_module['faq'])) || (isset($session_module['ratecard'])))
        <li class="dropdown nav-item @yield('settings')" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-settings"></i><span>Settings</span></a>
          <ul class="dropdown-menu">
            @if($user_role == 'SUPERADMIN' || (isset($session_module['settings'])))
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/settings"
              data-toggle="dropdown">General Settings
                  <submenu class="name"></submenu></a>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['about'])))
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/about"
              data-toggle="dropdown">About
                  <submenu class="name"></submenu></a>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['terms'])))
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/terms"
              data-toggle="dropdown">Terms and Conditions
                  <submenu class="name"></submenu></a>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['faq'])))
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/faq"
              data-toggle="dropdown">FAQs
                  <submenu class="name"></submenu></a>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['ratecard'])))
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/ratecard"
              data-toggle="dropdown">Rate Card
                  <submenu class="name"></submenu></a>
            </li>
            @endif
          </ul>
        </li>
        @endif
 
        @if($user_role == 'SUPERADMIN' || (isset($session_module['banners']))  || (isset($session_module['countries'])) || (isset($session_module['languages'])) || (isset($session_module['slots'])) || (isset($session_module['zones'])) || (isset($session_module['days']))  || (isset($session_module['cancellation_policies'])) || (isset($session_module['job_status'])) || (isset($session_module['fees_types'])) || (isset($session_module['banks'])) || (isset($session_module['fees_types'])) || (isset($session_module['banks'])) || (isset($session_module['categories'])) || (isset($session_module['subcategories'])) || (isset($session_module['services'])) || (isset($session_module['subservices'])) || (isset($session_module['provider_request_services'])) || (isset($session_module['brands'])))
        <li class="dropdown nav-item @yield('mastersettings')" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-monitor"></i><span>Master Settings</span></a>
          <ul class="dropdown-menu">
            @if($user_role == 'SUPERADMIN' || (isset($session_module['banners']))  || (isset($session_module['countries'])) || (isset($session_module['languages'])) || (isset($session_module['slots'])) || (isset($session_module['zones'])) || (isset($session_module['days']))  || (isset($session_module['cancellation_policies'])) || (isset($session_module['job_status'])) || (isset($session_module['fees_types'])) || (isset($session_module['banks'])) || (isset($session_module['fees_types'])) || (isset($session_module['banks'])) || (isset($session_module['brands'])))
            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">General Masters
                  <submenu class="name"></submenu></a>
              <ul class="dropdown-menu">
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/banners" data-toggle="dropdown">Banners
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/countries" data-toggle="dropdown">Countries 
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/languages" data-toggle="dropdown">Languages
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/slots" data-toggle="dropdown">Slots
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/zones" data-toggle="dropdown">Zones
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item d-none" href="{{URL('/')}}/admin/days" data-toggle="dropdown">Days
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item d-none" href="{{URL('/')}}/admin/cancellation_policies" data-toggle="dropdown">Cancellation Policies
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/job_status" data-toggle="dropdown">Job Status
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item d-none" href="{{URL('/')}}/admin/fees_types" data-toggle="dropdown">Fees Types
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/banks" data-toggle="dropdown">Banks
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/brands" data-toggle="dropdown">Brands
                      <submenu class="name"></submenu></a>
                </li>
              </ul>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['categories'])) || (isset($session_module['subcategories'])) || (isset($session_module['services'])) || (isset($session_module['subservices'])))
            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">Category Level Masters
                  <submenu class="name"></submenu></a>
              <ul class="dropdown-menu">
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/categories" data-toggle="dropdown">Categories
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/subcategories" data-toggle="dropdown">Sub Categories 
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/services" data-toggle="dropdown">Services
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/subservices" data-toggle="dropdown">Sub Services
                      <submenu class="name"></submenu></a>
                </li>
              </ul>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['provider_request_services'])))
            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">Provider Request Services
                  <submenu class="name"></submenu></a>
              <ul class="dropdown-menu">
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/provider_request_services" data-toggle="dropdown">Services
                      <submenu class="name"></submenu></a>
                </li>
              </ul>
            </li>
            @endif
          </ul>
        </li> 
        @endif

        @if($user_role == 'SUPERADMIN' || (isset($session_module['userroles']))  || (isset($session_module['modules'])) || (isset($session_module['role_module_mapping'])))
        <li class="dropdown nav-item @yield('settings')" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-settings"></i><span>Access Settings</span></a>
          <ul class="dropdown-menu">
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/userroles"
              data-toggle="dropdown">Roles
                  <submenu class="name"></submenu></a>
            </li>
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/modules"
              data-toggle="dropdown">Modules
                  <submenu class="name"></submenu></a>
            </li>
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/role_module_mapping"
              data-toggle="dropdown">Access Rights
                  <submenu class="name"></submenu></a>
            </li>            
          </ul>
        </li>
        @endif

        @if($user_role == 'SUPERADMIN' || (isset($session_module['servicers']))  || (isset($session_module['userwallet'])) || (isset($session_module['userwallettransactions'])) || (isset($session_module['users'])) || (isset($session_module['roleusers'])))
        <li class="dropdown nav-item @yield('users')" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-layout"></i><span>Users</span></a>
          <ul class="dropdown-menu">
            @if($user_role == 'SUPERADMIN' || (isset($session_module['servicers']))  || (isset($session_module['userwallet'])) || (isset($session_module['userwallettransactions'])))
            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown">Service Providers
                  <submenu class="name"></submenu></a>
              <ul class="dropdown-menu">
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/servicers/pending" data-toggle="dropdown">Service Providers Approval
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/servicers" data-toggle="dropdown">Service Providers
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/userwallet" data-toggle="dropdown">Service Providers Wallet
                      <submenu class="name"></submenu></a>
                </li>
                <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/userwallettransactions" data-toggle="dropdown">Service Providers Wallet Transactions
                      <submenu class="name"></submenu></a>
                </li>
              </ul>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['users'])))
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/users"
              data-toggle="dropdown">App Users
                  <submenu class="name"></submenu></a>
            </li>
            @endif
            @if($user_role == 'SUPERADMIN' || (isset($session_module['roleusers'])))
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/roleusers"
              data-toggle="dropdown">Admin Users
                  <submenu class="name"></submenu></a>
            </li>
            @endif
          </ul>
        </li>
        @endif

        @if($user_role == 'SUPERADMIN' || (isset($session_module['bookings'])))
        <li class="nav-item @yield('bookings')" data-menu="dropdown">
          <a class=" nav-link" href="{{URL('/')}}/admin/bookings"><i class="ft-bold"></i>
            <span>Bookings</span>
          </a>
        </li>
        @endif

        @if($user_role == 'SUPERADMIN' || (isset($session_module['booking_payments']))  || (isset($session_module['payouts'])))
        <li class="dropdown nav-item @yield('reports')" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-layout"></i><span>Reports</span></a>
           
          <ul class="dropdown-menu">
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/booking_payments" data-toggle="dropdown">Booking Payments
                  <submenu class="name"></submenu></a>
            </li> 
            <li data-menu=""><a class="dropdown-item" href="{{URL('/')}}/admin/payouts" data-toggle="dropdown">Payout Transactions
                  <submenu class="name"></submenu></a>
            </li>
          </ul>
             
        </li>
        @endif
      </ul>
    </div>
    <!-- /horizontal menu content-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
  </div>
  <!-- Horizontal navigation-->