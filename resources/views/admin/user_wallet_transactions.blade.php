@extends('layouts.admin_master')
@section('users', 'active')
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
                  <h4 class="card-title">User wallet Transactions</h4>     
                    <div class="row">   
                        <div class="form-group form-float float-left col-md-3">
                            <label class="form-label"> </label> 
                            <div class="form-line">
                                <select class="form-control" name="user_id" id="user_id" required onchange="getWalletAmount();">
                                    <option value=""> Select User </option>
                                    @if(!empty($users))
                                        @foreach($users as $user)
                                            <option value="{{$user->id}}">{{$user->mobile}} {{$user->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <label class="form-label"> </label> 
                            <div class="form-line">
                                <select name="type" id="type" class="form-control">
                                    <option value="">All</option>
                                    <option value="CREDIT">Credit</option>
                                    <option value="DEBIT">Debit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3   float-left">
                            <p id="date_filter">
                                <span id="date-label-from" class="date-label">From: </span><input class="date_range_filter date form-control " type="text" id="datepicker_from" />
                                <span id="date-label-to" class="date-label">To:<input class="date_range_filter date form-control " type="text" id="datepicker_to" /></span>
                            </p>
                        </div> 
                        <div class="col-md-3   float-left">
                            <button class="btn btn-info mt-5" id="clear">Clear</button>
                        </div> 
                    </div>
                </div>
                @if($rights['rights']['list'] == 1)
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
                        <div class="table-responsicve">
                            <table class="table table-striped table-bordered tblcountries">
                              <thead>
                                <tr>
                                  <th>Date</th> 
                                  <th>User</th>
                                  <th>Amount</th> 
                                  <th>Message</th>
                                  <th>Type</th> 
                                  <th>Transaction</th>
                                  <th>Reason</th>
                                </tr>
                              </thead>
                              
                              <tfoot>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
                                  <th></th>
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
@endsection

@section('scripts')

    <script>

        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('.tblcountries').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("userwallettransactions.data")}}',
                    data: function ( d ) {

                        var minDateFilter  = $('#datepicker_from').val();
                        var maxDateFilter  = $('#datepicker_to').val();
                        var user_id  = $('#user_id').val();
                        var type  = $('#type').val(); 
                        $.extend(d, {minDateFilter:minDateFilter, maxDateFilter:maxDateFilter,
                            user_id:user_id, type:type 
                        });

                    }
                },
                columns: [
                    { data: 'wallet_date', 'name':'wallet_date'}, 
                    { data: 'mobile', 'name':'mobile'},
                    { data: 'amount', 'name':'amount'},  
                    { data: 'message', 'name':'message'},
                    { data: 'type', 'name':'type'},  
                    { data: 'transaction_details', 'name':'transaction_details'}, 
                    { data: 'reason', 'name':'reason'},                             
                ],
                "order": [[ 0, "desc" ]]
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
            $("#datepicker_from").datetimepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                onClose: function(dateText, inst) {
                    tabledraw(); 
                },
            }).change(function() {
                
            }).keyup(function() {
                 
            });

            $("#datepicker_to").datetimepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                onClose: function(dateText, inst) {
                    tabledraw();  
                },
            }).change(function() {
                 
            }).keyup(function() {
                
            });

            $('#type').on('change', function() {
                tabledraw();
            });

            $('#user_id').on('change', function() {
                tabledraw();
            });

            function tabledraw() {
                var minDateFilter  = $('#datepicker_from').val();
                var maxDateFilter  = $('#datepicker_to').val();
                if(new Date(maxDateFilter) < new Date(minDateFilter))
                {
                    alert('To Date must be greater than From Date');
                    return false;
                }
                table.draw();
            }

            $('#clear').on('click', function() {
            $('#datepicker_from').val('');
            $('#datepicker_to').val('');
            $('#user_id').val('');
            $('#type').val(''); 
            table.draw();
        })
        });
 

    </script>

@endsection
