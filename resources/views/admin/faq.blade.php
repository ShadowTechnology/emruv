@extends('layouts.admin_master')
@section('settings', 'active')
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
                  <h4 class="card-title">FAQ 
                    @if($rights['rights']['add'] == 1)
                    <a href="#" data-toggle="modal" data-target="#smallModal"><button class="btn btn-primary" style="float: right;">Add</button></a> </h4>        
                    @endif
                </div>
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
                        <div class="table-responsicve">
                    <table class="table table-striped table-bordered tblcategory">
                      <thead>
                        <tr>
                          <th>FAQ For</th>  
                          <th>Type</th>
                          <th>Question</th>
                          <!-- <th>Question in Arabic</th> -->
                          <th>Answer</th>
                          <!-- <th>Answer in Arabic</th> -->
                          <th>Position</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tfoot>
                          <tr><th></th><th></th><th></th>
                              <th></th><th></th><th></th>
                              <th></th>
                          </tr>
                      </tfoot>
                      <tbody>
                        
                      </tbody>

                    </table>
                    </div>
                </div>
                  </div>
                </div>
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
                    <h4 class="modal-title" id="smallModalLabel">Add FAQ</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/faq')}}"
                                  method="post">

                        {{csrf_field()}}

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Question</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="question" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6 d-none">
                                <label class="form-label">Question in Arabic</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="alias_question">
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Answer</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="answer" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6 d-none">
                                <label class="form-label">Answer in Arabic</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="alias_answer">
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Faq For</label>
                                <div class="form-line">
                                    <select type="text" class="form-control" name="faq_for" required>
                                        <option value="USER">User</option>
                                        <option value="SERVICEPROVIDER">Service Provider</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Type</label>
                                <div class="form-line">
                                    <select type="text" class="form-control" name="faq_type" required>
                                        <option value="User">User</option>
                                        <option value="Service Provider">Service Provider</option>
                                        <option value="Bookings">Bookings</option>
                                        <option value="Payments">Payments</option>
                                        <option value="Technical Issues">Technical Issues</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Position</label>
                                <div class="form-line">
                                    <input type="number" class="form-control" name="position" required min="1">
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
                    <h4 class="modal-title" id="smallModalLabel">Edit FAQ</h4>
                </div>

                <form id="edit-style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/faq')}}"
                                  method="post">

                        {{csrf_field()}}
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Question</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="question" id="question" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6 d-none">
                                <label class="form-label">Question in Arabic</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="alias_question" id="alias_question">
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Answer</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="answer" id="answer" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6 d-none">
                                <label class="form-label">Answer in Arabic</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="alias_answer" id="alias_answer">
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Faq For</label>
                                <div class="form-line">
                                    <select type="text" class="form-control" name="faq_for" id="faq_for" required>
                                        <option value="USER">User</option>
                                        <option value="SERVICEPROVIDER">Service Provider</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Type</label>
                                <div class="form-line">
                                    <select type="text" class="form-control" name="faq_type" id="faq_type" required>
                                        <option value="User">User</option>
                                        <option value="Service Provider">Service Provider</option>
                                        <option value="Bookings">Bookings</option>
                                        <option value="Payments">Payments</option>
                                        <option value="Technical Issues">Technical Issues</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Position</label>
                                <div class="form-line">
                                    <input type="number" class="form-control" name="position" id="position" required min="1">
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-line">
                                    <select class="form-control" name="status" id="status" required>
                                      <option value="ACTIVE">ACTIVE</option>
                                      <option value="INACTIVE">INACTIVE</option>
                                    </select>
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

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('.tblcategory').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("faqs.data")}}',
                },
                columns: [
                    { data: 'faq_for'},
                    { data: 'faq_type'},
                    { data: 'question'},
                    /*{ data: 'alias_question'},*/
                    { data: 'answer'},
                    /*{ data: 'alias_answer'},*/
                    { data: 'position'},
                    { data: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="#" onclick="loadFaq('+tid+')" title="Edit FAQ"><i class="ft-edit"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ]

            });

            $('.tblcategory tfoot th').each( function (index) {
                if(index != 6) {
                    var title = $(this).text();
                    $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
                }
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

                           $('.tblcategory').DataTable().ajax.reload();

                           $('#smallModal').modal('hide');

                           $("#style-form")[0].reset();

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

                           $('.tblcategory').DataTable().ajax.reload();

                           $('#smallModal-2').modal('hide');

                           $("#edit-style-form")[0].reset();

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

        function loadFaq(id){

            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/faq')}}",
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

                $('#id').val(response.data.id);
                $('#faq_for').val(response.data.faq_for);
                $('#faq_type').val(response.data.faq_type);
                $('#question').val(response.data.question);
                $('#alias_question').val(response.data.alias_question);
                $('#answer').val(response.data.answer);
                $('#alias_answer').val(response.data.alias_answer);
                $('#status').val(response.data.status);
                $('#position').val(response.data.position);
                $('#smallModal-2').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }


    </script>

@endsection
