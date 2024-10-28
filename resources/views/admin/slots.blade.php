@extends('layouts.admin_master')
@section('mastersettings', 'active')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
    <link rel="stylesheet" type="text/css" href="{{ asset('/public/css/bootstrap-clockpicker.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@if($rights['rights']['view'] == 1)
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Slots 
                    @if($rights['rights']['add'] == 1)
                        <a href="#" data-toggle="modal" data-target="#smallModal" id="addslot"><button class="btn btn-primary" style="float: right;">Add</button></a> 
                    @endif
                  </h4>        
                          
                </div>
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
                        <div class="table-responsicve">
                            <table class="table table-striped table-bordered tblcountries">
                              <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Name</th>
                                    <th>From Time</th>
                                    <th>To Time</th>
                                    <th>Period</th>
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
@if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Slots</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/slots')}}"
                                  method="post">

                        {{csrf_field()}}
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Slot Name</label>
                                <div class="form-line"> 
                                    <input type="text" class="form-control" name="slot_name" id="slot_name" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">From Time</label>
                                <div class="form-line">
                                    <div class="input-group clockpicker" data-placement="left" data-align="middle" data-autoclose="true">
                                        <input type="text" name="from_time" id="from_time" class="form-control" required>
                                        <span class="input-group-addon">
                                            <span class="ft-clock p-2"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>  
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">To Time</label>
                                <div class="form-line">
                                    <div class="input-group clockpicker" data-placement="left" data-align="middle" data-autoclose="true">
                                        <input type="text" name="to_time" id="to_time" class="form-control" required>
                                        <span class="input-group-addon">
                                            <span class="ft-clock p-2"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Period</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="period_name" required id="period_name">
                                </div>
                            </div>

                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Checkhours</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="check_hours" required id="check_hours">
                                </div>
                            </div>

                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Limit</label>
                                <div class="form-line">
                                    <input type="number" class="form-control" name="check_limit" required min="0" id="check_limit">
                                </div>
                            </div>

                            <div class="form-group form-float col-md-6">
                                <label class="form-label">Position</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="position" id="position" required>
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
                       <button type="sumbit" class="btn btn-link waves-effect" id="add_style">SAVE</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
    <script type="text/javascript" src="{{asset('/public/js/bootstrap-clockpicker.min.js') }}"></script>

    <script>
        $('.clockpicker').clockpicker();

        $('#addslot').on('click', function() {
            $('#style-form')[0].reset();
            $('#id').val('');
        });

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('.tblcountries').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("slots.data")}}',
                },
                columns: [
                    { data: 'position'},
                    { data: 'slot_name'},
                    { data: 'from_time'},
                    { data: 'to_time'},
                    { data: 'period_name'},
                    { data: 'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="#" onclick="loadSlot('+tid+')" title="Edit Country"><i class="ft-edit"></i></a>';
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

            $('.tblcountries tfoot th').each( function (index) {
                if(index != 6){
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
            @if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
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
        });


        function loadSlot(id){

            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/slots')}}",
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
                $('#slot_name').val(response.data.slot_name);
                $('#from_time').val(response.data.from_time);
                $('#to_time').val(response.data.to_time);
                $('#period_name').val(response.data.period_name);
                $('#position').val(response.data.position);
                $('#status').val(response.data.status);
                $('#check_hours').val(response.data.check_hours);  
                $('#check_limit').val(response.data.counts_per_slot);
                $('#smallModal').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }


    </script>

@endsection
