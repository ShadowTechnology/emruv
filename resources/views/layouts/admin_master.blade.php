<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

@include('layouts.admin_head')

<body class="horizontal-layout horizontal-menu 2-columns   menu-expanded" data-open="hover"
data-menu="horizontal-menu" data-col="2-columns">

  <!-- Top Bar -->
  @include('layouts.admin_header')

  <div class="app-content content">
    <div class="content-wrapper">
      <div class="content-header row">
      </div>
      <div class="content-body">

        @yield('content')

      </div>
    </div>
  </div>


  @include('layouts.admin_footer')
  @yield('scripts')
</body>
<script type="text/javascript">
    var session_country = "<?php echo Session::get('session_country');?>";
    
    if(session_country == '') {
        setAdminCountry();
    }

    var session_country = "<?php echo Session::get('session_country');?>";
    
    if(session_country == '') {
       // swal('Oops','Please set a Country','error');
    }

    function setAdminCountry(){
        $.ajax({
                url : "{{URL::to('/admin/checkSetAdminCountry')}}",
                type: "POST",
                data:{'session_country':$('#session_country').val()},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response)
                {
                    if(response.status == 'SUCCESS'){
                        window.location.href = "{{URL::to('/admin/home')}}";
                        /*swal({
                          title: "Country set successfully",                          
                          type: "success",
                          confirmButtonColor: "#DD6B55",
                          confirmButtonText: "OK",
                          closeOnConfirm: false,
                        },
                        function(inputValue){
                          
                            window.location.href = "{{URL::to('/admin/home')}}";
                          
                        });     */                   
                    }
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    $('#status').text(jqXHR);
                }
            });
    }
</script>
</html>