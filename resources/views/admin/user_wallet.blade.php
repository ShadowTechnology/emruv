@extends('layouts.admin_master')
@section('users', 'active')
@section('content')
<?php 
use App\Http\Controllers\AdminController;

$rights = AdminController::getRights();

?>
<link rel="stylesheet" type="text/css" href="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/css/forms/selects/select2.min.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@if($rights['rights']['view'] == 1)
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">User wallet 
                    @if($rights['rights']['add'] == 1)
                    <a href="#" data-toggle="modal" data-target="#smallModal"><button class="btn btn-primary add_wallet" style="float: right;">Add</button></a> 
                    @endif
                  </h4>        
                   <div class="row">   
                        <div class="form-group form-float float-left col-md-3">
                            <label class="form-label"> </label> 
                            <div class="form-line">
                                <select class="form-control select2" name="sel_user_id" id="sel_user_id" required>
                                    <option value=""> Select User </option>
                                    @if(!empty($users))
                                        @foreach($users as $user)
                                            <option value="{{$user->id}}">{{$user->mobile}} {{$user->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                @if($rights['rights']['view'] == 1)
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
                        <div class="table-responsicve">
                            <table class="table table-striped table-bordered tblcountries">
                              <thead>
                                <tr>
                                  <th>User</th>
                                  <th>Wallet Ballance</th> 
                                </tr>
                              </thead>
                              <tfoot>
                                  <tr><th></th><th></th>
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
    <div class="modal fade in" id="smallModal"  role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Add Amount to wallet</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/userwallet')}}"
                                  method="post">

                        {{csrf_field()}}

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left  col-md-6">
                                <label class="form-label">User</label> <span id='user_wallet_amount'></span>
                                <div class="form-line">
                                    <select class="form-control select2  col-md-12 " name="user_id" id="user_id" required onchange="getWalletAmount();" style="width: 350px !important;">
                                        <option value=""> Select User </option>
                                        @if(!empty($users))
                                            @foreach($users as $user)
                                                <option value="{{$user->id}}">{{$user->mobile}} {{$user->name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Type</label>
                                <div class="form-line">
                                    <select class="form-control" name="type" id="type" required>
                                        <option value=""> Select type </option>
                                        <option value="CREDIT"> Credit </option>
                                        <option value="DEBIT"> Debit </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Amount</label>
                                <div class="form-line">
                                    <input type="number" class="form-control" name="amount" id="amount"  min=1 maxlength=5 onkeypress="return isNumber(event)" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Reason</label>
                                <div class="form-line">
                                    <select class="form-control  col-md-12 select2 reason" multiple="multiple"  name="reason[]" id="reason" required  style="width: 350px !important;"></select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Description</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="description" id="description" required>
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
    <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/vendors/js/forms/select/select2.full.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('/adminstack30/stack-admin-3.0/app-assets/js/scripts/forms/select/form-select2.js')}}" type="text/javascript"></script>
    <script>

        $('.add_wallet').on('click', function(){
            $("#style-form")[0].reset();
            $('#user_id').val(null).trigger('change');
            $('#reason').val(null).trigger('change');
        }); 

        $('.reason').select2({
            data: ["Penalty", "Compensation", "Commission", "Reward"],
            tags: true,
            maximumSelectionLength: 10,
            tokenSeparators: [',', ' '],
            placeholder: "Select or type keywords",
        });

        function getWalletAmount(){
            var user_id = $('#user_id').val();
            if(user_id > 0) {
                var request = $.ajax({
                    type: 'post',
                    url: " {{URL::to('/admin/user/get/walletamount')}}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data:{
                        code:user_id,
                    },
                    dataType:'json',
                    encode: true
                });
                request.done(function (response) {

                    if(response.status == 'SUCCESS') {
                        $('#user_wallet_amount').text(response.data.amount);
                    }   else {
                        swal("Oops!", response.message, "error");
                    }

                });
                request.fail(function (jqXHR, textStatus) {

                    swal("Oops!", "Sorry,Could not process your request", "error");
                });
            } else {
                $('#user_wallet_amount').text('');
            }
        }

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('.tblcountries').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("userwallet.data")}}',
                    data: function ( d ) {

                        var sel_user_id  = $('#sel_user_id').val(); 
                        $.extend(d, {sel_user_id:sel_user_id});

                    }
                },
                columns: [
                    { data: 'mobile'},
                    { data: 'wallet_amount'},  
                ],

            });

            $('.tblcountries tfoot th').each( function () {
                var title = $(this).text();
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
            $('#sel_user_id').on('change', function() {
                tabledraw();
            })

            function tabledraw() {
                table.draw();
            }
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

        });

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
