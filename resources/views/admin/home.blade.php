@extends('layouts.admin_master')
@section('dashboard', 'active')
@section('content')  
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
  @if($rights['rights']['view'] == 1)
        <!-- Stats -->
        <div class="row">
          <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="primary">{{$totalUsers}}</h3>
                      <span>Total Users</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-social-dropbox primary font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($totalUsers>0)
                      @php($widthval = $totalUsers * 100 / $totalUsers)
                    @endif
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$totalUsers}}" aria-valuemin="0" aria-valuemax="{{$totalUsers}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="primary">{{$activeuserscount}}</h3>
                      <span>Active Users</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-social-dropbox primary font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($activeuserscount>0 && $totalUsers>0)
                      @php($widthval = $activeuserscount * 100 / $totalUsers)
                    @endif
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$activeuserscount}}" aria-valuemin="0" aria-valuemax="{{$activeuserscount}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="primary">{{$totalServicers}}</h3>
                      <span>Total Servicers</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-social-dropbox primary font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($totalServicers>0 && $totalUsers>0)
                      @php($widthval = $totalServicers * 100 / $totalServicers)
                    @endif
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$totalServicers}}" aria-valuemin="0" aria-valuemax="{{$totalServicers}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="primary">{{$activeproviderscount}}</h3>
                      <span>Active Servicers</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-social-dropbox primary font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($activeproviderscount>0 && $totalUsers>0)
                      @php($widthval = $activeproviderscount * 100 / $totalUsers)
                    @endif
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$activeproviderscount}}" aria-valuemin="0" aria-valuemax="{{$activeproviderscount}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="danger">{{$totalBookings}}</h3>
                      <span>Total Bookings</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow danger font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($totalBookings>0)
                      @php($widthval = $totalBookings * 100 / $totalBookings)
                    @endif
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$totalBookings}}" aria-valuemin="0" aria-valuemax="{{$totalBookings}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="danger">{{$pendingBookings}}</h3>
                      <span>Pending Bookings</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow danger font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($totalBookings>0)
                      @php($widthval = $pendingBookings * 100 / $totalBookings)
                    @endif
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$pendingBookings}}" aria-valuemin="0" aria-valuemax="{{$pendingBookings}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="danger">{{$startedBookings}}</h3>
                      <span>Started Bookings</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow danger font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($totalBookings>0)
                      @php($widthval = $startedBookings * 100 / $totalBookings)
                    @endif
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$startedBookings}}" aria-valuemin="0" aria-valuemax="{{$startedBookings}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="danger">{{$completedBookings}}</h3>
                      <span>Completed Bookings</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow danger font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($totalBookings>0)
                      @php($widthval = $completedBookings * 100 / $totalBookings)
                    @endif
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$completedBookings}}" aria-valuemin="0" aria-valuemax="{{$completedBookings}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="danger">{{$canceledBookings}}</h3>
                      <span>Cancelled Bookings</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow danger font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($canceledBookings>0)
                      @php($widthval = $canceledBookings * 100 / $totalBookings)
                    @endif
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$canceledBookings}}" aria-valuemin="0" aria-valuemax="{{$canceledBookings}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="danger">{{$paymentPendingBookings}}</h3>
                      <span>Payment Pending Bookings</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow danger font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($totalBookings>0)
                      @php($widthval = $paymentPendingBookings * 100 / $totalBookings)
                    @endif
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$paymentPendingBookings}}" aria-valuemin="0" aria-valuemax="{{$paymentPendingBookings}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div> 

          <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="info">{{$gst_commission}}</h3>
                      <span>GST Collections</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow info font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($gst_commission>0)
                      @php($widthval = $gst_commission * 100 / $gst_commission)
                    @endif
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$gst_commission}}" aria-valuemin="0" aria-valuemax="{{$gst_commission}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="info">{{$admin_commission}}</h3>
                      <span>Admin Commission</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow info font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($admin_commission>0)
                      @php($widthval = $admin_commission * 100 / $admin_commission)
                    @endif
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$admin_commission}}" aria-valuemin="0" aria-valuemax="{{$admin_commission}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="media">
                    <div class="media-body text-left w-100">
                      <h3 class="info">{{$revenue}}</h3>
                      <span>Total Revenue</span>
                    </div>
                    <div class="media-right media-middle">
                      <i class="icon-user-follow info font-large-2 float-right"></i>
                    </div>
                  </div>
                  <div class="progress progress-sm mt-1 mb-0">
                    @php($widthval = 0)
                    @if($revenue>0)
                      @php($widthval = $revenue * 100 / $revenue)
                    @endif
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{$widthval}}%" aria-valuenow="{{$revenue}}" aria-valuemin="0" aria-valuemax="{{$revenue}}"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
           
        </div>
        <!--/ Stats -->
        
        <!--Recent Orders & Monthly Salse -->
        <div class="row match-height">
          <div class="col-xl-12 col-lg-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Recent Booking</h4>
                <span class="float-right"><a href="{{URL('/')}}/admin/bookings" target="_blank">View All <i class="ft-arrow-right"></i></a></span>
              </div>
              <div class="card-content">
                <div class="table-responsive">
                  <table id="recent-orders" class="table table-hover mb-0 ps-container ps-theme-default">
                    <thead>
                      <tr>
                        <th>Ref No</th>
                        <th>Customer</th>
                        <th>Provider</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if(!empty($recent_bookings))
                        @foreach($recent_bookings as $booking)
                          <tr>
                            <td class="text-truncate"><a href="{{URL('/')}}/admin/view/bookings/{{$booking->id}}/{{$booking->ref_no}}">{{$booking->ref_no}}</a></td>
                            <td class="text-truncate">{{$booking->mobile}}</td>
                            <td class="text-truncate">{{$booking->providermobile}}</td>
                            <td class="text-truncate">{{number_format($booking->total_amount,2)}}</td>
                            <td class="text-truncate">{{$booking->job_date}}</td>
                            <td class="text-truncate">
                              <span class="badge badge-default badge-warning">{{$booking->status}}</span>
                            </td>
                          </tr>
                        @endforeach
                      @else 
                        <tr><td colspan="5">No Recent Bookings</td></tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
         
        </div>
        <!--/Recent Orders & Monthly Salse -->
  @endif
      
@endsection



@section('scripts') 
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/charts/jquery.sparkline.min.js')}}"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/charts/raphael-min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/charts/morris.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/extensions/unslider-min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/timeline/horizontal-timeline.js')}}" type="text/javascript"></script>
  
    <!-- BEGIN PAGE LEVEL JS-->
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/scripts/ui/breadcrumbs-with-stats.js')}}"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/scripts/pages/dashboard-ecommerce.js')}}" type="text/javascript"></script>
  <!-- END PAGE LEVEL JS-->

@endsection
