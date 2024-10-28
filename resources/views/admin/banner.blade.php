@extends('layouts.admin_master')
@section('mastersettings', 'active')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@if($rights['rights']['view'] == 1)
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Banners 
                @if($rights['rights']['add'] == 1)
                    <a href="#" data-toggle="modal" data-target="#smallModal" id="addbanner"><button class="btn btn-primary" style="float: right;">Add</button></a> 
                @endif
                </h4>        
                          
                </div>
                @if($rights['rights']['list'] == 1)
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
                        <div class="table-responsicve">
                            <table class="table table-striped table-bordered tblcountries">
                              <thead>
                                <tr> 
                                  <th>Name</th> 
                                  <th>Image</th> 
                                  <th>Position</th>
                                  <th>Status</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tfoot>
                                  <tr><th></th><th></th><th></th>
                                      <th></th><th></th>
                                  </tr>
                              </tfoot>
                              <tbody>
                                
                              </tbody>
                            </table>
                        </div>
                    </div>
                  </div>
                </div>
                @endif
              </div>
            </div>
          </div>
    </section>
@endif
@if($rights['rights']['add'] == 1)
    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Add Banner</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/banners')}}"
                                  method="post">

                        {{csrf_field()}}

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Name</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div> 
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Image</label>
                                <div class="form-line">
                                    <input type="file" class="form-control" name="image" required>
                                </div>
                            </div> 
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Is Link</label>
                                <div class="form-line">
                                    <select class="form-control" name="is_link" required>
                                        <option value="">Select</option>
                                      <option value="YES">YES</option>
                                      <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Link To</label>
                                <div class="form-line">
                                    <select class="form-control" name="link_level"  id="link_level" onchange="loadLevels(this);">
                                        <option value="">Select</option>
                                      <option value="2">Sub Categories</option>
                                      <option value="3">Services</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Links</label>
                                <div class="form-line">
                                    <select class="form-control link_id" name="link_id" id="link_id">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Position</label>
                                <div class="form-line">
                                    <select class="form-control" name="position" required>
                                      <option value="TOP">TOP</option>
                                      <option value="BOTTOM">BOTTOM</option>
                                      <option value="CENTER">CENTER</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-line">
                                    <select class="form-control" name="status" required>
                                      <option value="ACTIVE">ACTIVE</option>
                                      <option value="INACTIVE">INACTIVE</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                       <button type="sumbit" class="btn btn-link waves-effect" id="add_style">SAVE</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endif
@if($rights['rights']['edit'] == 1)
    <div class="modal fade in" id="smallModal-2" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Edit Banner</h4>
                </div>

                <form id="edit-style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/banners')}}"
                                  method="post">

                        {{csrf_field()}}
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Name</label>
                                <div class="form-line">
                                    <input type="text" class="form-control " name="name" id="edit_name" required>
                                </div>
                            </div> 
                            
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Image</label>
                                <div class="form-line">
                                    <input type="file" class="form-control" name="image" id="edit_image">
                                </div>
                            </div> 
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Is Link</label>
                                <div class="form-line">
                                    <select class="form-control" name="is_link" id="edit_is_link" required>
                                      <option value="">Select</option>
                                      <option value="YES">YES</option>
                                      <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Link To</label>
                                <div class="form-line">
                                    <select class="form-control" name="link_level" id="edit_link_level" onchange="loadLevels(this);">
                                      <option value="">Select</option>
                                      <option value="2">Sub Categories</option>
                                      <option value="3">Services</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Links</label>
                                <div class="form-line">
                                    <input type="hidden" name="link_id_value" id="link_id_value">
                                    <select class="form-control link_id" name="link_id" id="edit_link_id">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Position</label>
                                <div class="form-line">
                                    <select class="form-control" name="position"  id="edit_position" required>
                                      <option value="TOP">TOP</option>
                                      <option value="BOTTOM">BOTTOM</option>
                                      <option value="CENTER">CENTER</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-line">
                                    <select class="form-control" name="status"  id="edit_status" required>
                                      <option value="ACTIVE">ACTIVE</option>
                                      <option value="INACTIVE">INACTIVE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <div class="form-line">
                                    <img src="" id="is_banner_image" height="100" width="100">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                       <button type="sumbit" class="btn btn-link waves-effect" id="edit_style">SAVE</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')

    <script>

        $('#addbanner').on('click', function() {
            $('#style-form')[0].reset();
            $('#link_id_value').val('');
            $('.link_id').html('');
            $('#edit_link_level').trigger('onchange');

        });

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('.tblcountries').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("banners.data")}}',
                },
                columns: [ 
                    { data: 'name',  name: 'name'}, 
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {
                            if(data.image != '' || data.image != null){
                                var tid = data.is_image;
                                return '<img src="'+tid+'" height="50" width="50">';
                            }   else {
                                return '';
                            }
                        },

                    }, 
                    { data: 'position',  name: 'position'},
                    { data: 'status',  name: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="#" onclick="loadBanner('+tid+')" title="Edit Banner"><i class="ft-edit"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 4 },
                    { "orderable": false, "targets": 1 }
                ]

            });

            $('.tblcountries tfoot th').each( function (index) {
                var title = $(this).text();
                if(index != 1 && index != 4)
                    $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
            } );

            // Apply the search
            table.columns().every( function () {
                var that = this;

                $( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                                .search( this.value )
                                .draw();
                    }
                } );
            } );
            @endif
            @if($rights['rights']['add'] == 1)
            $('#add_style').on('click', function () {

                var options = {

                    beforeSend: function (element) {

                        $("#add_style").text('Processing..');

                        $("#add_style").prop('disabled', true);

                    },
                    success: function (response) {



                        $("#add_style").prop('disabled', false);

                        $("#add_style").text('SUBMIT');

                        if (response.status == "SUCCESS") {

                           swal('Success',response.message,'success');

                           $('.tblcountries').DataTable().ajax.reload();

                           $('#smallModal').modal('hide');

                        }
                        else if (response.status == "FAILED") {

                            swal('Oops',response.message,'warning');

                        }

                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        $("#add_style").prop('disabled', false);

                        $("#add_style").text('SUBMIT');

                        swal('Oops','Something went to wrong.','error');

                    }
                };
                $("#style-form").ajaxForm(options);
            });
            @endif

            @if($rights['rights']['edit'] == 1)
            $('#edit_style').on('click', function () {

                var options = {

                    beforeSend: function (element) {

                        $("#edit_style").text('Processing..');

                        $("#edit_style").prop('disabled', true);

                    },
                    success: function (response) {

                        $("#edit_style").prop('disabled', false);

                        $("#edit_style").text('SUBMIT');

                        if (response.status == "SUCCESS") {

                           swal('Success',response.message,'success');

                           $('.tblcountries').DataTable().ajax.reload();

                           $('#smallModal-2').modal('hide');

                        }
                        else if (response.status == "FAILED") {

                            swal('Oops',response.message,'warning');

                        }

                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        $("#edit_style").prop('disabled', false);

                        $("#edit_style").text('SUBMIT');

                        swal('Oops','Something went to wrong.','error');

                    }
                };
                $("#edit-style-form").ajaxForm(options);
            });
            @endif


        });


        function loadBanner(id){

            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/banners')}}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data:{
                    code:id,
                },
                dataType:'json',
                encode: true
            });
            request.done(function (response) {
                $('#link_id_value').val('');
                $('#id').val(response.data.id); 
                $('#edit_name').val(response.data.name); 
                $('#edit_status').val(response.data.status);
                $('#is_banner_image').attr('src', response.data.is_image);
                $('#edit_is_link').val(response.data.is_link);
                $('#edit_link_level').val(response.data.link_level);
                $('#link_id_value').val(response.data.link_id);
                if(response.data.link_level > 0) {
                    //$('#edit_link_level').trigger('onchange');
                } 
                $('#edit_link_level').trigger('onchange');
                $('#edit_position').val(response.data.position);
                $('#smallModal-2').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }

        function loadLevels($obj) {
            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/loadLevels')}}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data:{
                    link_level:$($obj).val(),
                    link_id:$('#link_id_value').val(),
                },
                dataType:'json',
                encode: true
            });
            request.done(function (response) {
                if(response.status == 'SUCCESS')
                    $('.link_id').html(response.data);
                else 
                    $('.link_id').html('');
            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }
    </script>

@endsection
