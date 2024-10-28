@extends('layouts.admin_master')
@section('mastersettings', 'active')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
@if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
<?php $id = $status_value = $status_description = ''; 
$status = 'ACTIVE';

if(isset($job_status) && !empty($job_status)) { 
    if(is_object($job_status)) {
        $id = $job_status->id;
        $status_value = $job_status->status_value;
        $status_description = $job_status->status_description;
        $status = $job_status->status;
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
                            <h4 class="title">Job Status</h4>
                        </div>
                        <div class="card-body">
                            <form id="add-job_status-form" enctype="multipart/form-data" action="{{url('/admin/save/job_status')}}"
                                  method="post">
                                {{csrf_field()}}

                                <input type="hidden" name="id" id="id" value="{{$id}}">

                                <div class="form-group">
                                    <label class="form-label">Status Value *</label>
                                    <select class="form-control" name="status_value" id="status_value" required>
                                        <option value="" @if($status_value == '') selected @endif>Select Status Value</option>
                                        <!-- <option value="COMPLETED" @if($status_value == 'COMPLETED') selected @endif>COMPLETED</option> -->
                                        <option value="INPROGRESS" @if($status_value == 'INPROGRESS') selected @endif>INPROGRESS</option>
                                        <option value="UNABLETOCOMPLETE" @if($status_value == 'UNABLETOCOMPLETE') selected @endif>UNABLE TO COMPLETE</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Description *</label>
                                    <input type="text" class="form-control" name="status_description" id="status_description" required value="{{$status_description}}">
                                </div>

                                <div class="form-group">
                                    
                                    <label class="form-label">Status *</label>
                                    
                                    <input type="radio" name="status" id="active" value="ACTIVE" class="with-gap" @if($status == 'ACTIVE') checked @endif>
                                    <label for="active">Active</label>

                                    <input type="radio" name="status" id="inactive" value="INACTIVE" class="with-gap" @if($status == 'INACTIVE') checked @endif>
                                    <label for="inactive" class="m-l-20">In Active</label>
                                </div>
                            
                                <button class="btn btn-primary waves-effect" type="submit" id="add-job_status">SUBMIT</button>
                                
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
        $('#add-job_status').on('click', function () {

            var options = {

                beforeSend: function (element) {

                    $("#add-job_status").text('Processing..');

                    $("#add-job_status").prop('disabled', true);

                },
                success: function (response) {

                    $('#emailHelp').text('');

                    $("#add-job_status").prop('disabled', false);

                    $("#add-job_status").text('SUBMIT');

                    if (response.status == "SUCCESS") {

                        swal({
                            title: "Info!",
                            text: "Job Status has been Saved",
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

                    $("#add-job_status").prop('disabled', false);

                    $("#add-job_status").text('SUBMIT');

                    swal("Oops!", 'Sorry could not process your request', "error");
                }
            };
            $("#add-job_status-form").ajaxForm(options);
        });
        @endif

        
    </script>
@endsection