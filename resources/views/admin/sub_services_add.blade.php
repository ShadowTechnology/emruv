@extends('layouts.admin_master')

@section('css')

    <style type="text/css">
        
        .title{

            font-size: 18px;
            font-weight: bold;
            color: #3f93af;

        }

    </style>

@endsection

@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
@if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
<?php $id = $service_id = $name = $description = $image = $sub_category_id = $position = $price = $offer_price = ''; 
$status = 'ACTIVE';  $price_based_on = 2;
if(isset($subservice) && !empty($subservice)) { 
    if(is_object($subservice)) {
        $id = $subservice->id;
        $service_id = $subservice->service_id;
        $name = $subservice->name;
        $description = $subservice->description;
        $status = $subservice->status;
        $image = $subservice->image;
        $price_based_on = $subservice->price_based_on;
        $price = $subservice->price;
        $offer_price = $subservice->offer_price;
        $sub_category_id = $sel_sub_category_id;
        $position = $subservice->position;
    }
}
?>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <section class="content">
        <div class="container-fluid">
            <!-- Basic Validation -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card card-default">
                        <div class="header">
                            <h4 class="title">Sub Service</h4>
                        </div>
                        <div class="card-body">
                            <form id="add-category-form" enctype="multipart/form-data" action="{{url('/admin/subservice/add')}}"
                                  method="post">
                                {{csrf_field()}}

                                <input type="hidden" name="id" id="id" value="{{$id}}">

                                <div class="form-group">
                                    <label class="form-label">Sub Category *</label>
                                    <select class="form-control" name="sub_category_id" id="sub_category_id" onchange="loadServices(this)" required style="width: 28%;">
                                        <option value="">Select Sub Category</option>
                                        @foreach($subcategory as $cat)
                                            <option value="{{$cat->id}}" @if($sub_category_id == $cat->id) selected @endif>{{$cat->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Service *</label>
                                    <select class="form-control" name="service_id" id="service_id" required style="width: 28%;">
                                        <option value="">Select Service</option>
                                        @foreach($services as $cat)
                                            <option value="{{$cat->id}}" @if($service_id == $cat->id) selected @endif>{{$cat->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Name *</label>
                                    <input type="text" class="form-control" name="name" required style="width: 28%;" value="{{$name}}">
                                </div>

                                <div class="row">
                                @if(count($languages)>0)
                                    @foreach($languages as $lang)
                                    <?php  
                                    if(isset($language_content[$lang->id]) && !empty($language_content[$lang->id]['title'])) {
                                        $title = $language_content[$lang->id]['title'];
                                    }   else {
                                        $title = $name;
                                    }
                                    ?>

                                    <div class="form-group col-md-4">
                                        <label class="form-label">Name In * {{$lang->language}}</label>
                                        <input type="text" class="form-control" name="name_lang[{{$lang->id}}]" required style="" value="{{$title}}">
                                    </div>
                                    @endforeach
                                @endif
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="5">{{$description}}</textarea>
                                </div>

                                @if(count($languages)>0)
                                    @foreach($languages as $lang)
                                    <?php  
                                    if(isset($language_content[$lang->id]) && !empty($language_content[$lang->id]['title'])) {
                                        $description = $language_content[$lang->id]['description'];
                                    }   else {
                                        $description = $description;
                                    }
                                    ?>

                                    <div class="form-group">
                                        <label class="form-label">Description In {{$lang->language}}</label>
                                        <textarea name="description_lang[{{$lang->id}}]" class="form-control" rows="5" >{{$description}}</textarea>
                                    </div>
                                    @endforeach
                                @endif

                                <div class="form-group" style="display: none;">
                                    <label>Image</label>
                                    <input type="file" class="form-file" name="image">
                                    @if($image != '')
                                        <img src="<?php echo config("constants.APP_IMAGE_URL").'uploads/services/'.$image; ?>" height="150" width="150">
                                    @endif
                                </div>
                               
                                <div class="form-group d-none">
                                    
                                    <label class="form-label">Price Based On *</label>
                                    
                                    <select class="form-control" name="price_based_on" required style="width: 28%;">
                                        <option value="1" @if($price_based_on == '1') selected @endif>Per Hour Based</option>
                                        <option value="2" @if($price_based_on == '2') selected @endif>Fixed Price</option>
                                    </select>
                                </div>   

                                <div class="form-group">
                                    <label class="form-label">Price *</label>
                                    <input type="text" class="form-control" name="price" required style="width: 28%;" value="{{$price}}">
                                </div> 

                                <div class="form-group">
                                    <label class="form-label">Offer Price *</label>
                                    <input type="text" class="form-control" name="offer_price" required style="width: 28%;" value="{{$offer_price}}">
                                </div> 

                                <div class="form-group">
                                    <label class="form-label">Position *</label>
                                    <input type="text" class="form-control" name="position" required style="width: 28%;" value="{{$position}}">
                                </div>  

                                <div class="form-group">
                                    
                                    <label class="form-label">Status *</label>
                                    
                                    <input type="radio" name="status" id="active" value="ACTIVE" class="with-gap" @if($status == 'ACTIVE') checked @endif>
                                    <label for="active">Active</label>

                                    <input type="radio" name="status" id="inactive" value="INACTIVE" class="with-gap" @if($status == 'INACTIVE') checked @endif>
                                    <label for="inactive" class="m-l-20">In Active</label>
                                </div>
                            
                                <button class="btn btn-primary waves-effect" type="submit" id="add-category">SUBMIT</button>
                                
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



        function loadServices($obj) {
            var subcatid = $($obj).val();
            if(subcatid>0) {
                var request = $.ajax({
                    type: 'post',
                    url: "{{URL('/')}}/admin/loadServices",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data:{'subcatid':subcatid},
                    dataType: 'json',
                    encode: true
                });
                request.done(function (response) {

                    if(response.status =='SUCCESS'){
                        $('#service_id').html(response.data);
                        @if($service_id>0) 
      
                            $('#service_id').val('{{$service_id}}');
                          
                        @endif
                    }else{

                        $('#service_id').html('<option value="">Select Service</option>');
                    }

                });

                request.fail(function (jqXHR, textStatus) {

                    swal('Oops,Something went to wrong,Please try after sometime');
                });
            }   else {
                $('#service_id').html('<option value="">Select Service</option>');
            }
        }
        @if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
        $('#add-category').on('click', function () {

            var options = {

                beforeSend: function (element) {

                    $("#add-category").text('Processing..');

                    $("#add-category").prop('disabled', true);

                },
                success: function (response) {

                    $('#emailHelp').text('');

                    $("#add-category").prop('disabled', false);

                    $("#add-category").text('SUBMIT');

                    if (response.status == "SUCCESS") {

                        swal({
                            title: "Info!",
                            text: "Sub Service has been Saved",
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

                    $("#add-category").prop('disabled', false);

                    $("#add-category").text('SUBMIT');

                    swal("Oops!", 'Sorry could not process your request', "error");
                }
            };
            $("#add-category-form").ajaxForm(options);
        });
    @endif
    @if($sub_category_id>0) 
      
        $('#sub_category_id').trigger('onchange');
      
    @endif
        
    </script>

    <script src="{{ asset('/adminlte/plugins/datatables/jquery.dataTables.js') }}"></script>

    <script src="{{ asset('adminlte/plugins/datatables/dataTables.bootstrap4.js') }}"></script>
@endsection

