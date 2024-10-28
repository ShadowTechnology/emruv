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
<?php $id = $name = $type = $tax_percent = $image = $description = ''; 
$position = 1; $status = 'ACTIVE'; $home_display = 'NO';

if(isset($category) && !empty($category)) { 
    if(is_object($category)) {
        $id = $category->id;
        $name = $category->name;
        $type = $category->type;
        $status = $category->status;
        $tax_percent = $category->tax_percent;
        $image = $category->image;
        $description = $category->description;
        $home_display = $category->home_display;
        $position = $category->position;
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
                            <h4 class="title">Category</h4>
                        </div>
                        <div class="card-body">
                            <form id="add-category-form" enctype="multipart/form-data" action="{{url('/admin/category/add')}}"
                                  method="post">
                                {{csrf_field()}}

                                <input type="hidden" name="id" id="id" value="{{$id}}">
                                <div class="row">
                                <div class="form-group d-none">
                                    <label class="form-label">Type *</label>
                                    <select type="text" class="form-control" name="type" required style="">
                                        <option value="1" @if($type == 1) selected @endif>Normal Booking</option>
                                        <option value="2" @if($type == 2) selected @endif>Package Booking</option>
                                    </select>
                                </div>
                                </div>
                                <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="form-label">Name *</label>
                                    <input type="text" class="form-control" name="name" required style="" value="{{$name}}">
                                </div>
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
                                    <label class="form-label">Tax Percentage *</label>
                                    <input type="text" class="form-control" name="tax_percent" required style="width: 28%;" value="{{$tax_percent}}">
                                </div>
                                
                                <div class="form-group">
                                    <label>Image *</label> 
                                    <input type="file" class="form-file" name="image">
                                    @if($image != '')
                                        <img src="<?php echo config("constants.APP_IMAGE_URL").'uploads/categories/'.$image; ?>"  height="150" width="150">
                                    @endif
                                </div>
                               
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="5" required>{{$description}}</textarea>
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
                                            <textarea name="description_lang[{{$lang->id}}]" class="form-control" rows="5" required>{{$description}}</textarea>
                                        </div>
                                    @endforeach
                                @endif

                                <div class="form-group">
                                    
                                    <label class="form-label">Home Display *</label>
                                    
                                    <input type="radio" name="home_display" id="yes" value="YES" class="with-gap" @if($home_display == 'YES') checked @endif>
                                    <label for="yes">YES</label>

                                    <input type="radio" name="home_display" id="no" value="NO" class="with-gap" @if($home_display == 'NO') checked @endif>
                                    <label for="no" class="m-l-20">NO</label>
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
                            text: "Category has been Saved",
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

        
    </script> 
@endsection