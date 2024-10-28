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
                    <div class="panel-title">{{ config("constants.site_name") }} Settings
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
                            <form name="frm_terms" id="frm_terms" method="post" action="{{url('/admin/update/settings')}}"> 
                                {{csrf_field()}}
                            <div class="col-md-12">
                                <div class="row">
                                <div class="form-group col-md-6 float-left">
                                    <label>Site Status<span class="manstar">*</span></label>
                                    <select name="site_on_off" id="site_on_off" class="form-control">
                                        <option value="ON" @if($settings->site_on_off == "ON") selected @endif>ON</option>
                                        <option value="OFF" @if($settings->site_on_off == "OFF") selected @endif>OFF</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label>Default Expiry After <span class="manstar">*</span></label>
                                    <input type="number" name="def_expiry_after" id="def_expiry_after" class="form-control" value="{{$settings->def_expiry_after}}">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label>Default Pagination Limit <span class="manstar">*</span></label>
                                    <input type="number" name="def_pagination_limit" id="def_pagination_limit" class="form-control" value="{{$settings->def_pagination_limit}}">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label>Help Contact <span class="manstar">*</span></label>
                                    <input type="text" name="helpcontact" id="helpcontact" class="form-control" value="{{$settings->helpcontact}}">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label>Admin Email <span class="manstar">*</span></label>
                                    <input type="text" name="admin_email" id="admin_email" class="form-control" value="{{$settings->admin_email}}">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label class="form-label">Admin Commission *</label>
                                    <input type="text" class="form-control" name="admin_commission" id="admin_commission" value="{{$settings->admin_commission}}" required  onkeypress="return isNumber(event, this)">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label>Contact Address <span class="manstar">*</span></label>
                                    <input type="text" name="contact_address" id="contact_address" class="form-control" value="{{$settings->contact_address}}">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label class="form-label">Hour Increment By *</label>
                                    <input type="text" class="form-control" name="hour_increment_by" id="hour_increment_by" value="{{$settings->hour_increment_by}}" required  onkeypress="return isNumber(event, this)">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label class="form-label">GST Percentage *</label>
                                    <input type="text" class="form-control" name="gst_percentage" id="gst_percentage" value="{{$settings->gst_percentage}}" required  onkeypress="return isNumber(event, this)">
                                </div>

                                <div class="form-group col-md-6 float-left d-none">
                                    <label class="form-label">Booking User Details View Point Percentage *</label>
                                    <input type="text" class="form-control" name="booking_user_details_point_percentage" id="booking_user_details_point_percentage" value="{{$settings->booking_user_details_point_percentage}}" onkeypress="return isNumber(event, this)">
                                </div>

                                <div class="form-group col-md-6 float-left d-none">
                                    <label class="form-label">Minimum Cut Off Points *</label>
                                    <input type="text" class="form-control" name="min_cutoff_points" id="min_cutoff_points" value="{{$settings->min_cutoff_points}}" onkeypress="return isNumber(event, this)">
                                </div>  

                                <div class="form-group col-md-6 float-left">
                                    <label>Max Count Zones <span class="manstar">*</span></label>
                                    <input type="number" name="max_count_zone" id="max_count_zone" class="form-control" value="{{$settings->max_count_zone}}" min="1">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label>Max Count Categories <span class="manstar">*</span></label>
                                    <input type="number" name="max_count_subcategory" id="max_count_subcategory" class="form-control" value="{{$settings->max_count_subcategory}}" min="1">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label class="form-label">Additional Charges *</label>
                                    <input type="text" class="form-control" name="additional_charges" id="additional_charges" value="{{$settings->additional_charges}}" required  onkeypress="return isNumber(event, this)">
                                </div>

                                <div class="form-group col-md-6 float-left">
                                    <label class="form-label">Additional Charges Text *</label>
                                    <input type="text" class="form-control" name="additional_charge_text" id="additional_charge_text" value="{{$settings->additional_charge_text}}" required>
                                </div>
                                @php($cats = [])  
                                @if(!empty($settings->additional_category_ids))
                                    @php($cats = explode(',', $settings->additional_category_ids))
                                @endif 
                                <div class="form-group col-md-6 float-left">
                                    <label>Enable Additional Charges for Categories: <span class="manstar">*</span></label>
                                    <select name="category_ids[]" id="category_ids" class="form-control" multiple>
                                        @if(!empty($subcategories))
                                            @foreach($subcategories as $subcat)
                                                <option value="{{$subcat->id}}" @if(in_array($subcat->id, $cats)) selected @endif>{{$subcat->name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>                           
                            </div>
                            </div>
                            @if($rights['rights']['edit'] == 1)
                            <div class="col-md-12 float-left">
                                <button type="submit" class="btn btn-success center-block" id="Submit">Submit</button>
                            </div>
                            @endif
                            </form>
                        </div></div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
</section>
@endsection

@section('scripts') 
      <script>

        $(function() { 
            @if($rights['rights']['view'] == 1)
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

                           swal('Success','Settings Info Saved Successfully','success');

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

        function isDecimal(evt, obj) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)  && (charCode != 46 || $(obj).val().indexOf('.') != -1)) {
                return false;
            }
            return true;
        }

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }

    </script>
 

@endsection

