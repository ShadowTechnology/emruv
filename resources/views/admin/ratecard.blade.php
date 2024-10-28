@extends('layouts.admin_master')
@section('settings', 'active')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="content">
    @if($rights['rights']['view'] == 1)
        <!-- Exportable Table -->
        <div class="content container-fluid">

            <div class="panel">

                <!-- Panel Heading -->
                <div class="panel-heading">

                    <!-- Panel Title -->
                    <div class="panel-title">Rate Card
                    </div>

                    

                </div>
                <div class="panel-body">

            @if($rights['rights']['view'] == 1)
            <div class="row">

                <div class="col-xs-12 col-md-12">
                
                <div class="card"> 
                    <div class="card-body">
                        <div class="row"><div class="col-md-12">
                            <form name="frm_terms" id="frm_terms" method="post" action="{{url('/admin/save/ratecard')}}"> 
                                {{csrf_field()}}

                            <div class="col-md-12">
                                @if(!empty($ratecard)) 
                                <a href="{{URL('/')}}/public/image/ratecard/{{$ratecard}}" target="_blank">View Rate card </a>
                                @endif
                            </div>
                            @if($rights['rights']['edit'] == 1)
                            <div class="col-md-12 mt-3">
                                
                                <div class="form-group">
                                    <label>Ratecard <span class="manstar">*</span></label>
                                    <input type="file" name="ratecard" id="ratecard" required> 
                                </div>
                                 
                            </div>
                            
                            <button type="submit" class="btn btn-success center-block" id="Submit">Submit</button>
                            @endif
                            </form>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
</div>
@endif
</section>
@endsection

@section('scripts') 
      <script>

        $(function() { 

            $('#Submit').on('click', function () {

                var options = {

                    beforeSend: function (element) {

                        $("#Submit").text('Processing..');

                        $("#Submit").prop('disabled', true);

                    },
                    success: function (response) {

                        $("#Submit").prop('disabled', false);

                        $("#Submit").text('SUBMIT');

                        if (response.status == "SUCCESS") {

                           swal('Success','Rate Card Saved Successfully','success');

                           window.location.reload();

                        }
                        else if (response.status == "FAILED") {

                            swal('Oops',response.message,'warning');

                        }

                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        $("#Submit").prop('disabled', false);

                        $("#Submit").text('SUBMIT');

                        swal('Oops','Something went to wrong.','error');

                    }
                };
                $("#frm_terms").ajaxForm(options);
            });       
        });

    </script> 

@endsection

