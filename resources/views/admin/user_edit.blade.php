@extends('layouts.admin_master')
@section('content')

    <section class="content">
        <div class="container-fluid">

            <form id="user-form"
                  action="{{url('/admin/update/user')}}"
                  method="post">
                {{csrf_field()}}
                <input type="hidden"  id="user_id" name="user_id" placeholder="Your Name" value="{{$user->id}}">


                <div class="row">
                    <!-- left column -->


                    <div class="col-md-6">
                        <!-- general form elements -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Update User Status</h3>
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <div class="form-group">
                                <label>User Active/ In Avtive</label>
                                <select class="form-control" name="status" id="product_category">


                                    <option value="ACTIVE" {{ ($user->status == "ACTIVE") ? "selected" : '' }}>Active</option>
                                    <option value="INACTIVE" {{ ($user->status == "INACTIVE") ? "selected" : '' }}>In Active</option>




                                </select>
                            </div>
                        </div>
                        <!-- /.card -->


                    </div>
                    <!--/.col (left) -->
                   </div>
                <div style="margin: auto;padding: 10px;">
                    <button type="submit" class="btn btn-info" id="edit-user">Submit</button>
                    <input action="action" onclick="window.history.go(-1); return false;" type="button" value="BACK" class="btn btn-info waves-effect" />
                </div>
            </form>

            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>

@endsection

@section('scripts')



    <script>
        $('#edit-user').on('click', function () {
            var $edituser = $("#edit-user");
            var options = {

                beforeSend: function (element) {

                    $edituser.text('Processing..');

                    $edituser.prop('disabled', true);

                },
                success: function (response) {


                    $edituser.text('SUBMIT');

                    $edituser.prop('disabled', false);

                    if (response.status == "SUCCESS") {

                        swal({
                            title: "Info!",
                            text: "The User Status has been updated",
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

                    $edituser.prop('disabled', false);

                    $edituser.text('SUBMIT');

                    swal("Oops!", 'Sorry could not process your request', "error");
                }
            };
            $("#user-form").ajaxForm(options);
        });


    </script>

@endsection
