<input type="hidden" name="uploadImageURL" id="uploadImageURL" value="{{URL('/')}}/admin/uploadImage"> 

<input type="hidden" name="image_upload_type" id="image_upload_type" value="">
<input type="hidden" name="image_upload_name" id="image_upload_name" value="">
 
  <!-- ////////////////////////////////////////////////////////////////////////////-->
  <footer class="footer footer-static footer-light navbar-shadow">
    <p class="clearfix blue-grey lighten-2 text-sm-center mb-0 px-2">
      <span class="float-md-left d-block d-md-inline-block">Copyright &copy; 2020 <a class="text-bold-800 grey darken-2" href="https://themeforest.net/user/pixinvent/portfolio?ref=pixinvent"
        target="_blank">Smarther </a>, All rights reserved. </span>
      <span class="float-md-right d-block d-md-inline-block d-none d-lg-block">Hand-crafted & Made with <i class="ft-heart pink"></i></span>
    </p>
  </footer>
  <!-- BEGIN VENDOR JS-->
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/vendors.min.js')}}" type="text/javascript"></script>
  <!-- BEGIN VENDOR JS-->
  <!-- BEGIN PAGE VENDOR JS-->
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/ui/jquery.sticky.js')}}"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/forms/icheck/icheck.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/forms/toggle/switchery.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/datatable/datatables.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/datatable/dataTables.buttons.min.js')}}"
  type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/buttons.flash.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/jszip.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/pdfmake.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/vfs_fonts.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/buttons.html5.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/tables/buttons.print.min.js')}}" type="text/javascript"></script>
  
  <!-- END PAGE VENDOR JS-->
  <!-- BEGIN STACK JS-->
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/core/app-menu.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/core/app.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/scripts/customizer.js')}}" type="text/javascript"></script>
  <!-- END STACK JS-->

  <script src="{{asset('/public/js/sweetalert.min.js') }}"></script>

  <script src="{{asset('/public/js/jquery-form.js') }}"></script>

  <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script>
  <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js" ></script>
  <script src="{{asset('/public/js/jquery-ui-timepicker-addon.js') }}"></script>
  <script type="text/javascript">
     if(!window.File && window.FileReader && window.FileList && window.Blob){ //if browser doesn't supports File API
       swal('Oops',"Your browser does not support new File API! Please upgrade.",'warning');
    }
    var newExportAction = function (e, dt, button, config) {
         var self = this;
         var oldStart = dt.settings()[0]._iDisplayStart;

         dt.one('preXhr', function (e, s, data) {
             // Just this once, load all data from the server...
             data.start = 0;
             data.length = 2147483647;

             dt.one('preDraw', function (e, settings) {
                 // Call the original action function
                 oldExportAction(self, e, dt, button, config);

                 dt.one('preXhr', function (e, s, data) {
                     // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                     // Set the property to what it was before exporting.
                     settings._iDisplayStart = oldStart;
                     data.start = oldStart;
                 });

                 // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                 setTimeout(dt.ajax.reload, 0);

                 // Prevent rendering of the full data to the DOM
                 return false;
             });
         });

         // Requery the server with the new one-time export settings
         dt.ajax.reload();
     };
  </script>