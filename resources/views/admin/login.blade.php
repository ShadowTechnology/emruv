<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta name="description" content='{{ config("constants.site_name") }} Admin Console'>
  <title>Login Page - {{ config("constants.site_name") }} Admin Console</title>
  <link rel="apple-touch-icon" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/images/ico/apple-icon-120.png')}}">
  <link rel="shortcut icon" type="image/x-icon" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/images/ico/favicon.ico')}}">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i%7COpen+Sans:300,300i,400,400i,600,600i,700,700i"
  rel="stylesheet">
  <!-- BEGIN VENDOR CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/vendors.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/css/forms/icheck/icheck.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/css/forms/icheck/custom.css')}}">
  <!-- END VENDOR CSS-->
  <!-- BEGIN STACK CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/app.css')}}">
  <!-- END STACK CSS-->
  <!-- BEGIN Page Level CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/core/menu/menu-types/horizontal-menu.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/pages/login-register.css')}}">
  <!-- END Page Level CSS-->
  <!-- BEGIN Custom CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/assets/css/style.css')}}">

  <link rel="stylesheet" href="{{ asset('/public/css/sweetalert.css') }}">
  <!-- END Custom CSS-->
</head>
<body class="horizontal-layout horizontal-menu 1-column   menu-expanded blank-page blank-page"
data-open="click" data-menu="horizontal-menu" data-col="1-column">
  <!-- ////////////////////////////////////////////////////////////////////////////-->
  <div class="app-content container center-layout mt-2">
    <div class="content-wrapper">
      <div class="content-header row">
      </div>
      <div class="content-body">
        <section class="flexbox-container">
          <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="col-md-4 col-10 box-shadow-2 p-0">
              <div class="card border-grey border-lighten-3 m-0">
                <div class="card-header border-0">
                  <div class="card-title text-center">
                    <div class="p-1">
                      <img src="{{URL('/')}}/public/image/logo.png" height="100" width="200" alt='{{ config("constants.site_name") }}'>
                    </div>
                  </div>
                  <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                    <span>Login with {{ config("constants.site_name") }}</span>
                  </h6>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    <form id="login-form" action="{{ url('admin/login') }}" method="POST">
                        {{csrf_field()}}
                      <fieldset class="form-group position-relative has-icon-left mb-0">
                        <input type="text" class="form-control form-control-lg" name="email" id="email" placeholder="Email"
                        required>
                        <div class="form-control-position">
                          <i class="ft-user"></i>
                        </div>
                      </fieldset>
                      <fieldset class="form-group position-relative has-icon-left">
                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password"
                        required>
                        <div class="form-control-position">
                          <i class="fa fa-key"></i>
                        </div>
                      </fieldset>
                      <small id="emailHelp" class="form-text text-m red"></small>
                      <small id="emailHelpS" class="form-text text-t"></small>
                      <button type="submit" class="btn btn-primary btn-lg btn-block" id ="login-id"><i class="ft-unlock"></i> Login</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!-- ////////////////////////////////////////////////////////////////////////////-->
  <!-- BEGIN VENDOR JS-->
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/vendors.min.js')}}" type="text/javascript"></script>
  <!-- BEGIN VENDOR JS-->
  <!-- BEGIN PAGE VENDOR JS-->
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/ui/jquery.sticky.js')}}"></script>
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/charts/jquery.sparkline.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/forms/spinner/jquery.bootstrap-touchspin.js')}}"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/forms/validation/jqBootstrapValidation.js')}}"
  type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/forms/icheck/icheck.min.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/forms/toggle/switchery.min.js')}}" type="text/javascript"></script>
  <!-- END PAGE VENDOR JS-->
  <!-- BEGIN STACK JS-->
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/core/app-menu.js')}}" type="text/javascript"></script>
  <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/core/app.js')}}" type="text/javascript"></script>
  
  <!-- END STACK JS-->
  <!-- BEGIN PAGE LEVEL JS-->
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/scripts/ui/breadcrumbs-with-stats.js')}}"></script>
  <script type="text/javascript" src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/scripts/forms/validation/form-validation.js')}}"></script>

  <script src="{{asset('/public/js/sweetalert.min.js') }}"></script>

  <script src="{{asset('/public/js/jquery-form.js') }}"></script>

  <!-- END PAGE LEVEL JS-->

  <script>

    $('#login-id').on('click', function () {

        var options = {

            beforeSend: function (element) {

                $("#login-id").text('Processing..');

                $("#login-id").prop('disabled', true);

            },
            success: function (response) {

                $('#emailHelp').text('');

                $("#login-id").prop('disabled', false);

                $("#login-id").text('Sign In');

                if (response.status == "SUCCESS") {

                    $('#emailHelpS').text('Please be patient the portal will be open.!');

                    window.location.href = "{{URL::to('admin/home')}}";

                }
                else if (response.status == "FAILED") {

                    $('#emailHelp').text(response.message);

                }
            },
            error: function (jqXHR, textStatus, errorThrown) {

                $("#login-id").prop('disabled', false);

                $("#login-id").text('Sign In');

                swal("Oops!", 'Sorry could not process your request', "error");
            }
        };
        $("#login-form").ajaxForm(options);
    });
</script>
</body>
</html>