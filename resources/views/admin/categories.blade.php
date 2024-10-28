@extends('layouts.admin_master')
@section('mastersettings', 'active')

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
                             CATEGORIES
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="{!! url('admin/category/add') !!}"><button class="btn btn-primary" style="float: right;">Add</button></a>
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categories" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <!-- <th>Type</th> -->
                                        <th>Tax Percent</th>
                                        <th>Position</th>
                                        <th>Home Display</th>
                                        <th>Image</th>
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
                              <tbody></tbody>
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

@section('scripts')

    <script>


        $(function() {
            @if($rights['rights']['list'] == 1)
            var table = $('#categories').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("categories.data")}}',
                },
                columns: [
                    { data: 'name', name:'name'},
                    /*{
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var dtype = data.type;
                            if(dtype == 1)
                                return 'Normal Booking';
                            else 
                                return 'Package Booking';
                        },

                    },*/
                    { data: 'tax_percent', name:'tax_percent'},
                    { data: 'position', name:'position'},
                    { data: 'home_display', name:'home_display'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var imagesrc = data.is_image;

                            return '<img src="'+imagesrc+'" width=40 height=40 />';
                        },

                    },
               
                    { data: 'status', name:'status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="{{URL::to('admin/category/edit')}}/'+tid+'"><i class="ft-edit"></i></a>';
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
                                "url": '{{route("categories_excel.data")}}',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'Categories.xls');
                                    tempLink.click();
                                }
                            });
                        }
                    },
                     
                ],

            });

            $('#categories tfoot th').each( function (index) {
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
        });
    </script>

 


@endsection
