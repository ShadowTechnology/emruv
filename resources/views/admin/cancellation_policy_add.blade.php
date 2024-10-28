@extends('layouts.admin_master')
@section('mastersettings', 'active')

@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
@if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
<?php $id = $policy_type = $policy_description = $policy_hours = $is_refund_avail = $refund_amount = ''; 
$status = 'ACTIVE';

if(isset($cancellation_policy) && !empty($cancellation_policy)) { 
    if(is_object($cancellation_policy)) {
        $id = $cancellation_policy->id;
        $policy_type = $cancellation_policy->policy_type;
        $policy_description = $cancellation_policy->policy_description;
        $status = $cancellation_policy->status;
        $policy_hours = $cancellation_policy->policy_hours;
        $is_refund_avail = $cancellation_policy->is_refund_avail;
        $refund_amount = $cancellation_policy->refund_amount;
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
                            <h4 class="title">Cancellation Policy</h4>
                        </div>
                        <div class="card-body">
                            <form id="add-cancellation_policy-form" enctype="multipart/form-data" action="{{url('/admin/save/cancellation_policy')}}"
                                  method="post">
                                {{csrf_field()}}

                                <input type="hidden" name="id" id="id" value="{{$id}}">

                                <div class="form-group">
                                    <label class="form-label">Policy Type *</label>
                                    <input type="text" class="form-control" name="policy_type" required  value="{{$policy_type}}">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Policy Description *</label>
                                    <textarea class="form-control" name="policy_description" rows="3" required >{{$policy_description}}</textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Hours Before Cancellation *</label>
                                    <input type="number" class="form-control" name="policy_hours" required style="width: 28%;" value="{{$policy_hours}}" min="1" max="24" placeholder="24">
                                </div>

                                <div class="form-group">
                                    
                                    <label class="form-label">Is Refund Available *</label>
                                    
                                    <input type="radio" name="is_refund_avail" id="full" value="FULL" class="with-gap" @if($is_refund_avail == 'FULL') checked @endif>
                                    <label for="full">Full</label>

                                    <input type="radio" name="is_refund_avail" id="partial" value="PARTIAL" class="with-gap" @if($is_refund_avail == 'PARTIAL') checked @endif>
                                    <label for="partial">Partial</label>

                                    <input type="radio" name="is_refund_avail" id="no" value="NO" class="with-gap" @if($is_refund_avail == 'NO') checked @endif>
                                    <label for="no" class="m-l-20">No</label>
                                </div>
                               
                                <div class="form-group">
                                    <label class="form-label">Refund Amount</label>
                                    <input type="text" class="form-control" name="refund_amount" required style="width: 28%;" value="{{$refund_amount}}">
                                </div>

                                <div class="form-group">
                                    
                                    <label class="form-label">Status *</label>
                                    
                                    <input type="radio" name="status" id="active" value="ACTIVE" class="with-gap" @if($status == 'ACTIVE') checked @endif>
                                    <label for="active">Active</label>

                                    <input type="radio" name="status" id="inactive" value="INACTIVE" class="with-gap" @if($status == 'INACTIVE') checked @endif>
                                    <label for="inactive" class="m-l-20">In Active</label>
                                </div>
                            
                                <button class="btn btn-primary waves-effect" type="submit" id="add-cancellation_policy">SUBMIT</button>
                                
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
        $('#add-cancellation_policy').on('click', function () {

            var options = {

                beforeSend: function (element) {

                    $("#add-cancellation_policy").text('Processing..');

                    $("#add-cancellation_policy").prop('disabled', true);

                },
                success: function (response) {

                    $('#emailHelp').text('');

                    $("#add-cancellation_policy").prop('disabled', false);

                    $("#add-cancellation_policy").text('SUBMIT');

                    if (response.status == "SUCCESS") {

                        swal({
                            title: "Info!",
                            text: "Cancellation Policy has been Saved",
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

                    $("#add-cancellation_policy").prop('disabled', false);

                    $("#add-cancellation_policy").text('SUBMIT');

                    swal("Oops!", 'Sorry could not process your request', "error");
                }
            };
            $("#add-cancellation_policy-form").ajaxForm(options);
        });
        @endif

        
    </script> 
@endsection