@extends('layouts.admin_master')
@section('mastersettings', 'active')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
@if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
<?php $id = $fees_type = ''; 
$status = 'ACTIVE';

if(isset($fees_types) && !empty($fees_types)) { 
    if(is_object($fees_types)) {
        $id = $fees_types->id;
        $fees_type = $fees_types->fees_type;
        $status = $fees_types->status;
    }
}
?>

   <section class="content">
        <div class="container-fluid">
            <!-- Basic Validation -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card card-default">
                        <div class="header">
                            <h4 class="title">Fees Type</h4>
                        </div>
                        <div class="card-body">
                            <form id="add-fees_types-form" enctype="multipart/form-data" action="{{url('/admin/save/fees_types')}}"
                                  method="post">
                                {{csrf_field()}}

                                <input type="hidden" name="id" id="id" value="{{$id}}">

                                <div class="form-group">
                                    <label class="form-label">Fees Type *</label>
                                    <input type="text" class="form-control" name="fees_type" required  value="{{$fees_type}}">
                                </div>

                                <div class="form-group">
                                    
                                    <label class="form-label">Status *</label>
                                    
                                    <input type="radio" name="status" id="active" value="ACTIVE" class="with-gap" @if($status == 'ACTIVE') checked @endif>
                                    <label for="active">Active</label>

                                    <input type="radio" name="status" id="inactive" value="INACTIVE" class="with-gap" @if($status == 'INACTIVE') checked @endif>
                                    <label for="inactive" class="m-l-20">In Active</label>
                                </div>
                            
                                <button class="btn btn-primary waves-effect" type="submit" id="add-fees_types">SUBMIT</button>
                                
                                <input action="action" onclick="window.history.go(-1); return false;" type="button" value="BACK" class="btn btn-info waves-effect" />

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
@endsection

@section('scripts')

    <script>
        @if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
        $('#add-fees_types').on('click', function () {

            var options = {

                beforeSend: function (element) {

                    $("#add-fees_types").text('Processing..');

                    $("#add-fees_types").prop('disabled', true);

                },
                success: function (response) {

                    $('#emailHelp').text('');

                    $("#add-fees_types").prop('disabled', false);

                    $("#add-fees_types").text('SUBMIT');

                    if (response.status == "SUCCESS") {

                        swal({
                            title: "Info!",
                            text: "Fees Types has been Saved",
                            type: "success",

                        }, function () {
                             window.location.reload();
                        });
                    }
                    else if (response.status == "FAILED") {
                        swal("Oops!", response.message, "info");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {

                    $("#add-fees_types").prop('disabled', false);

                    $("#add-fees_types").text('SUBMIT');

                    swal("Oops!", 'Sorry could not process your request', "error");
                }
            };
            $("#add-fees_types-form").ajaxForm(options);
        });
        @endif

        
    </script> 
@endsection