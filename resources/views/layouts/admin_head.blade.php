<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <title>@yield('title') {{ config("constants.site_name") }} Admin Console</title>
  <link rel="apple-touch-icon" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/images/ico/apple-icon-120.png')}}">
  <link rel="shortcut icon" type="image/x-icon" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/images/ico/favicon.ico')}}">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i%7COpen+Sans:300,300i,400,400i,600,600i,700,700i"
  rel="stylesheet">
  <!-- BEGIN VENDOR CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/vendors.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/forms/icheck/icheck.css')}}">
  <link rel="stylesheet" type="text/css" href="../../../app-assets/vendors/css/forms/toggle/switchery.min.css">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/css/tables/datatable/datatables.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/css/extensions/unslider.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/css/weather-icons/climacons.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/fonts/meteocons/style.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/css/charts/morris.css')}}">
  <!-- END VENDOR CSS-->
  <!-- BEGIN STACK CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/app.css')}}">
  <!-- END STACK CSS-->
  <!-- BEGIN Page Level CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/core/menu/menu-types/horizontal-menu.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/core/colors/palette-gradient.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/fonts/simple-line-icons/style.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/core/colors/palette-gradient.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/css/pages/timeline.css')}}">
  <!-- END Page Level CSS-->
  <!-- BEGIN Custom CSS-->
  <link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/assets/css/style.css')}}">
  <!-- END Custom CSS-->

  <link rel="stylesheet" href="{{ asset('/public/css/sweetalert.css') }}">

  <link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"></link>

  <link rel="stylesheet" type="text/css" href="{{asset('/public/css/jquery-ui-timepicker-addon.css')}}">

  @yield('css')

    <style type="text/css">
      body {
        background-color: #fff !important;
      }
        .dataTables_filter {
            display: none;
        }
        tfoot {
            display: table-header-group;
        }
        .dt-buttons {
          margin-bottom: 1rem !important;
          margin-right: 3rem !important;
          float: left;
        }
    </style>

</head>