@extends('layouts.admin_master')
@section('settings', 'active')
@section('content')

<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>

<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="content">
        <!-- Exportable Table -->
        <div class="content container-fluid">

            <div class="panel">

                <!-- Panel Heading -->
                <div class="panel-heading">

                    <!-- Panel Title -->
                    <div class="panel-title">About {{ config("constants.site_name") }}
                    </div>

                    

                </div>
                <div class="panel-body">

            @if($rights['rights']['view'] == 1)
            <div class="row">

                <div class="col-xs-12 col-md-12">
            
                    <div class="card">
                        <div class="card-header">
                        </div>

                        <div class="card-body">
                            <div class="row"><div class="col-md-12">
                                <form name="frm_terms" id="frm_terms" method="post" action="{{url('/admin/save/about')}}"> 
                                    {{csrf_field()}}
                                <div class="col-md-12">
                                    
                                    <div class="form-group">
                                        <label>About <span class="manstar">*</span></label>
                                        <textarea name="about" id="about" class="form-control">{{$about}}</textarea>
                                    </div> 
                                    
                                </div>
                                @if($rights['rights']['edit'] == 1)
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
</section>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
      <script>

        $(function() {
            CKEDITOR.replace( 'about' ); 
            @if($rights['rights']['edit'] == 1)
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

                           swal('Success','About Info Saved Successfully','success');

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
            @endif     
        });

    </script>


    <script src="{{ asset('/adminlte/plugins/datatables/jquery.dataTables.js') }}"></script>

    <script src="{{ asset('adminlte/plugins/datatables/dataTables.bootstrap4.js') }}"></script>

    <script src="{{ asset('/adminlte/plugins/fastclick/fastclick.js') }}"></script>

    <script type="text/javascript" src="{{asset('/js/bootstrap-clockpicker.min.js') }}"></script>

@endsection

