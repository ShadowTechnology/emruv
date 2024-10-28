@extends('layouts.admin_master')
@section('content')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
@if($rights['rights']['view'] == 1)
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            Users
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="#" data-toggle="modal" data-target="#smallModal" id="adduser"><button class="btn btn-primary" style="float: right;">Add</button></a> 
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="users" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Country</th>
                                    <th>Referral Code</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
@endsection
@if($rights['rights']['add'] == 1 || $rights['rights']['edit'] == 1)
    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">User</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/user')}}"
                                  method="post">

                        {{csrf_field()}}
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">User Name</label>
                                <div class="form-line"> 
                                    <input type="text" class="form-control" name="user_name" id="user_name" required>
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">User Email</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="email" id="email" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Country Code</label>
                                <div class="form-line">
                                    <select class="form-control" name="country_id" id="country_id" required>
                                        @if(!empty($countries))
                                            @foreach($countries as $country)
                                                <option value="{{$country->id}}">+{{$country->phonecode}} {{$country->name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float col-md-6">
                                <label class="form-label">User Mobile</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="mobile" id="mobile" required>
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
@section('scripts')

    <script>

        $(function() {
            $('#adduser').on('click', function() {
                $('#style-form')[0].reset();
                $('#id').val('');
            });

            @if($rights['rights']['list'] == 1)
            var table = $('#users').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("users.data")}}',
                },
                columns: [
                    { data: 'name', name:'name'},
                    { data: 'email', name:'email'},
                    { data: 'mobile', name:'mobile'},
                    { data: 'country_code', name:'country_code'},
                    { data: 'referal_code', name:'referal_code'},
                    { data: 'status', name:'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            var tcode = data.reg_no;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="{{URL::to('admin/users/edit')}}/'+tid+'/'+tcode+'"><i class="ft-edit"></i></a>&nbsp; &nbsp; <a target="_blank" href="{{URL::to('admin/users/bookings')}}/'+tid+'/'+tcode+'"><i class="ft-layout"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ],
                dom: 'Blfrtip',
                buttons: [ 
                    { 
          
                        extend: 'excel',
                        text: 'Export Excel',
                        className: 'btn btn-warning btn-md ml-3',
                        action: function (e, dt, node, config) {
                            $.ajax({
                                "url": '{{route("users_excel.data")}}',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'Users.xls');
                                    tempLink.click();
                                }
                            });
                        }
                    },
                     
                ],
            });

            $('#users tfoot th').each( function () {
                var title = $(this).text();
                if($(this).index() != 6)
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

        function loadBank(id){

            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/user')}}",
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
                $('#user_name').val(response.data.name);
                $('#email').val(response.data.email);
                $('#mobile').val(response.data.mobile);
                $('#country_id').val(response.data.country);
                $('#status').val(response.data.status);
                $('#smallModal').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }
    </script> 
@endsection
