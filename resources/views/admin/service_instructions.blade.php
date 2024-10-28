@extends('layouts.admin_master')

@section('mastersettings', 'active') 

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            Sub Category - Additional Instructions
                        </h2>
                        <a href="{!! url('admin/subcategories') !!}"><button class="btn btn-primary ml-3" style="float: right;">Back</button></a>
                        <a href="#" data-toggle="modal" data-target="#smallModal"><button class="btn btn-primary" style="float: right;">Add</button></a>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="services" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Sub Category</th>
                                        <th>Instruction Type</th>
                                        <th>Instruction</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Add Instruction</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/add/subcategory_instruction')}}"
                                  method="post">

                        {{csrf_field()}}

                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Sub Category *</label>
                                <div class="form-line">
                                    <input type="hidden" name="service_id" id="service_id" value="{{$services->id}}">{{$services->name}}
                                </div>
                            </div>

                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Instruction Type *</label>
                                <div class="form-line">
                                    <select class="form-control" name="instruction_type" required>
                                        <option value="1">Services the provider expected to Provide</option>
                                        <option value="2">Services provider past Experience</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Instruction *</label>
                                <div class="form-line">
                                    <input type="text" name="instruction" class="form-control" required>
                                </div>
                            </div>
                        
                            <div class="form-group form-float">

                                <label class="form-label">Status *</label>

                                <input type="radio" name="display" id="active" value="ACTIVE" class="with-gap" checked>
                                        <label for="active">ACTIVE</label>

                                <input type="radio" name="display" id="inactive" value="INACTIVE" class="with-gap">
                                <label for="inactive" class="m-l-20">INACTIVE</label>

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

    <div class="modal fade in" id="smallModal-2" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Edit Instruction</h4>
                </div>

                <form id="edit-style-form" enctype="multipart/form-data"
                                  action="{{url('admin/update/subcategory_instruction')}}"
                                  method="post">

                        {{csrf_field()}}

                    <input type="hidden" class="form-control" id="instruction_id" name="instruction_id" required value="">

                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Sub Category *</label>
                                <div class="form-line">
                                    <input type="hidden" name="edit_service_id" id="edit_service_id" value="{{$services->id}}">{{$services->name}}
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Instruction Type *</label>
                                <div class="form-line">
                                    <select class="form-control" name="edit_instruction_type" id="edit_instruction_type" required >
                                        <option value="1">Services the provider expected to Provide</option>
                                        <option value="2">Services provider past Experience</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Instruction *</label>
                                <div class="form-line">
                                    <input type="text" name="edit_instruction" id="edit_instruction" class="form-control" required >
                                </div>
                            </div>
                        
                            <div class="form-group form-float">

                                <label class="form-label">Status *</label>

                                <input type="radio" name="display" id="edit_active" value="ACTIVE" class="with-gap" checked>
                                        <label for="edit_active">ACTIVE</label>

                                <input type="radio" name="display" id="edit_inactive" value="INACTIVE" class="with-gap">
                                <label for="edit_inactive" class="m-l-20">INACTIVE</label>

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

@endsection

@section('scripts')

    <script>


        $(function() {

            var table = $('#services').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("subcategory_instructions.data", ["id"=> $services->id])}}',
                },
                columns: [
                    { data: 'sub_category_name'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var itype = data.instruction_type;
                            if(itype == 1) return 'Services the provider expected to Provide';
                            else if(itype == 2) return 'Services provider past Experience';
                        },

                    },
                    { data: 'instruction'},
                    { data: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            return '<a href="#" onclick="loadInstruction('+tid+')" title="Edit Instruction"><i class="ft-edit"></i></a>';
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ]

            });

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

                           swal('Success','The Instruction has added','success');

                           $('#services').DataTable().ajax.reload();

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

                           swal('Success','The Instruction has updated','success');

                           $('#services').DataTable().ajax.reload();

                           $('#smallModal-2').modal('hide');

                           $("#edit-style-form")[0].reset();

                        }
                        else if (response.status == "failed") {

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

        });

        function loadInstruction(id){

            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/subcategory_instruction')}}",
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
                
                 $('#instruction_id').val(response.data.id);
                 $('#edit_instruction_type').val(response.data.instruction_type);
                 $('#edit_instruction').val(response.data.instruction);
                 if(response.data.status == 'ACTIVE') {
                    $('#edit_active').prop('checked', true);
                 }  else {
                    $('#edit_inactive').prop('checked', true);
                 }

                 $('#smallModal-2').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }
    </script> 

@endsection
