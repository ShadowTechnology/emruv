@extends('layouts.admin_master')
@section('mastersettings', 'active')
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
<?php $id = $category_id = $name = $description = $text1 = $text2 = $text3 = $image = $commission_percentage = $position = $ratecard = $video_link  = ''; 
$status = 'ACTIVE';  $home_display = 'NO';

if(isset($subcategory) && !empty($subcategory)) { 
    if(is_object($subcategory)) {
        $id = $subcategory->id;
        $category_id = $subcategory->category_id;
        $name = $subcategory->name;
        $description = $subcategory->description;
        $status = $subcategory->status;
        $text1 = $subcategory->text1;
        $text2 = $subcategory->text2;
        $text3 = $subcategory->text3;
        $image = $subcategory->image;
        $position = $subcategory->position;
        $home_display = $subcategory->home_display;
        $commission_percentage = $subcategory->commission_percentage;
        $ratecard = $subcategory->ratecard;
        $video_link = $subcategory->is_video_token;
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
                            <h4 class="title">Sub Category</h4>
                        </div>
                        <div class="card-body">
                            <form id="add-category-form" enctype="multipart/form-data" action="{{url('/admin/subcategory/add')}}"
                                  method="post">
                                {{csrf_field()}}

                                <input type="hidden" name="id" id="id" value="{{$id}}">

                                <div class="form-group">
                                    <label class="form-label">Category *</label>
                                    <select class="form-control" name="category_id" required style="width: 28%;">
                                        <option value="">Select Category</option>
                                        @foreach($category as $cat)
                                            <option value="{{$cat->id}}" @if($category_id == $cat->id) selected @endif>{{$cat->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="form-label">Name *</label>
                                    <input type="text" class="form-control" name="name" required style="" value="{{$name}}">
                                </div></div>
                                <div class="row">
                                @if(count($languages)>0)
                                    @foreach($languages as $lang)
                                        <?php  //echo "<pre>"; print_r($language_content[$lang->id]);
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
                                    <label class="form-label">Description *</label>
                                    <textarea name="description" class="form-control" rows="5" required>{{$description}}</textarea>
                                </div>

                                @if(count($languages)>0)
                                    @foreach($languages as $lang)
                                    <?php  //echo "<pre>"; print_r($language_content[$lang->id]);
                                        if(isset($language_content[$lang->id]) && !empty($language_content[$lang->id]['title'])) {
                                            $description = $language_content[$lang->id]['description'];
                                        }   else {
                                            $description = $description;
                                        }
                                        ?>
                                    <div class="form-group">
                                        <label class="form-label">Description In * {{$lang->language}}</label>
                                        <textarea name="description_lang[{{$lang->id}}]" class="form-control" rows="5" required>{{$description}}</textarea>
                                    </div>
                                    @endforeach
                                @endif

                                <div class="form-group">
                                    <label class="form-label">Text 1 *</label>
                                    <input type="text" class="form-control" name="text1" required value="{{$text1}}" maxlength="250">
                                </div>

                                @if(count($languages)>0)
                                    @foreach($languages as $lang)
                                    <?php  //echo "<pre>"; print_r($language_content[$lang->id]);
                                        if(isset($language_content[$lang->id]) && !empty($language_content[$lang->id]['title'])) {
                                            $text1 = $language_content[$lang->id]['text1'];
                                        }   else {
                                            $text1 = $text1;
                                        }
                                        ?>
                                    <div class="form-group">
                                        <label class="form-label">Text 1 In * {{$lang->language}}</label>
                                        <input type="text" class="form-control" name="text1_lang[{{$lang->id}}]" required value="{{$text1}}" maxlength="250">
                                    </div>
                                    @endforeach
                                @endif

                                <div class="form-group">
                                    <label class="form-label">Text 2 *</label>
                                    <input type="text" class="form-control" name="text2" required value="{{$text2}}" maxlength="250">
                                </div>

                                @if(count($languages)>0)
                                    @foreach($languages as $lang)
                                    <?php  //echo "<pre>"; print_r($language_content[$lang->id]);
                                        if(isset($language_content[$lang->id]) && !empty($language_content[$lang->id]['title'])) {
                                            $text2 = $language_content[$lang->id]['text2'];
                                        }   else {
                                            $text2 = $text2;
                                        }
                                        ?>
                                    <div class="form-group">
                                        <label class="form-label">Text 2 In * {{$lang->language}}</label>
                                        <input type="text" class="form-control" name="text2_lang[{{$lang->id}}]" required value="{{$text2}}" maxlength="250">
                                    </div>
                                    @endforeach
                                @endif

                                <div class="form-group">
                                    <label class="form-label">Text 3 *</label> 
                                    <input type="text" class="form-control" name="text3" required value="{{$text3}}" maxlength="250">
                                </div>

                                @if(count($languages)>0)
                                    @foreach($languages as $lang)
                                    <?php  //echo "<pre>"; print_r($language_content[$lang->id]);
                                        if(isset($language_content[$lang->id]) && !empty($language_content[$lang->id]['title'])) {
                                            $text3 = $language_content[$lang->id]['text3'];
                                        }   else {
                                            $text3 = $text3;
                                        }
                                        ?>
                                    <div class="form-group">
                                        <label class="form-label">Text 3 In * {{$lang->language}}</label> 
                                        <input type="text" class="form-control" name="text3_lang[{{$lang->id}}]" required value="{{$text3}}" maxlength="250">
                                    </div>
                                    @endforeach
                                @endif  
                                
                                <div class="form-group">
                                    <label class="form-label">YouTube Video Token 
                                      (ex.)https://www.youtube.com/watch?v=<b style="color: #f00;">ABCD1234-567</b></label> 
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="video_link" placeholder="ABCD1234-567" value="{{$video_link}}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Commission Percentage *</label>
                                    <input type="text" class="form-control" name="commission_percentage" required value="{{$commission_percentage}}" maxlength="10">
                                </div>

                                <div class="form-group">
                                    <label>Image *</label>
                                    <input type="file" class="form-file" name="image">
                                    @if($image != '')
                                        <img src="<?php echo config("constants.APP_IMAGE_URL").'uploads/categories/'.$image; ?>" height="150" width="150">
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Rate Card *</label>
                                    <input type="file" class="form-file" name="ratecard">
                                    @if(!empty($ratecard)) 
                                        <a href="{{URL('/')}}/public/uploads/categories/{{$ratecard}}" target="_blank">View Rate card </a>
                                    @endif
                                </div>

                                <div class="form-group d-none">
                                    
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
                            text: "Sub Category has been Saved",
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