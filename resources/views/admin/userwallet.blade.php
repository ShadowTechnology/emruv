@extends('layouts.admin_master')
@section('mastersettings', 'active')
@section('content')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">User Wallet <a href="#" data-toggle="modal" data-target="#smallModal"><button class="btn btn-primary" style="float: right;">Add Wallet Amount</button></a> </h4>        
                          
                </div>
                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
                        <div class="table-responsicve">
                            <table class="table table-striped table-bordered tblcountries">
                              <thead>
                                <tr>
                                  <th>Transaction Id</th>
                                  <th>User</th>
                                  <th>Transaction Date</th>
                                  <th>Amount</th>
                                  <th>Type</th>
                                  <th>Description</th>
                                </tr>
                              </thead>
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

    <div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Add Amount to Wallet</h4>
                </div>

                <form id="style-form" enctype="multipart/form-data"
                                  action="{{url('/admin/save/userwallet')}}"
                                  method="post">

                        {{csrf_field()}}

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">User</label>
                                <div class="form-line">
                                    <select name="user_id" id="user_id" class="form-control" required>
                                        <option value="">Select User</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Type</label>
                                <div class="form-line">
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="DEBIT">DEBIT</option>
                                        <option value="CREDIT">CREDIT</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Amount</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="amount" id="amount" required>
                                </div>
                            </div>
                            <div class="form-group form-float float-left col-md-6">
                                <label class="form-label">Description</label>
                                <div class="form-line">
                                    <textarea class="form-control" name="message" id="message" required></textarea>
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
 
@endsection

@section('scripts')

    <script>

        $(function() {

            var table = $('.tblcountries').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("countries.data")}}',
                },
                columns: [
                    { data: 'transaction_details'},
                    { data: 'user_id'},
                    { data: 'wallet_date'},
                    { data: 'amount'},
                    { data: 'message'} 
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
                  
                    if ( that.search() !== this.value && this.name == '') {
                        that
                                .search( this.value )
                                .draw();
                    }
                });
                
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
 
        });

 
    </script>

@endsection
