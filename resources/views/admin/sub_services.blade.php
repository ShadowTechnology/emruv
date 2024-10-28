@extends('layouts.admin_master')

@section('css')

    <link rel="stylesheet" href="{!! asset('/adminlte/plugins/datatables/dataTables.bootstrap4.css') !!}">

    <style>
        .user_class_active {
            color: #fff;
        }

        .user_class_active > a {
            color: #ff3c41 ! important;
        }

    </style>

@endsection

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
                             SUB SERVICES
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="{!! url('admin/subservice/add') !!}"><button class="btn btn-primary" style="float: right;">Add</button></a>
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categories" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Sub Category</th>
                                        <th>Service</th>
                                        <th>Name</th>
                                        <th>Price Based On</th>
                                        <th>Price</th>
                                        <th>Offer Price</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                  <tr><th></th><th></th><th></th>
                                      <th></th><th></th><th></th>
                                      <th></th><th></th><th></th>
                                  </tr>
                              </tfoot>
                              <tbody>
                                
                              </tbody>
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
                    "url": '{{route("subservices.data")}}',
                },
                columns: [
                    { data: 'sub_category_name', name: 'em_sub_category.name'},
                    { data: 'service_name', name: 'em_sub_cat_services.name'},
                    { data: 'name', name: 'em_sub_service.name'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var basedon = data.price_based_on;
                            if(basedon == 1) return 'Per Hour Price';
                            else if(basedon == 2) return 'Fixed Price';
                        },

                    },
                    { data: 'price', name:'price'},
                    { data: 'offer_price', name:'offer_price'},
                    { data: 'position', name:'em_sub_service.position'},
                    /*{
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var imagesrc = data.is_image;
                            if($.trim(data.image)!='') {
                                return '<img src="'+imagesrc+'" width=40 height=40 />';
                            }   else {
                                return '';
                            }
                            
                        },

                    },*/
                    
                    { data: 'status', name:'em_sub_service.status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="{{URL::to('admin/subservice/edit')}}/'+tid+'"><i class="ft-edit"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 8 }
                ],
                dom: 'Blfrtip',
                buttons: [ 
                    { 
          
                        extend: 'excel',
                        text: 'Export Excel',
                        className: 'btn btn-warning btn-md ml-3',
                        action: function (e, dt, node, config) {
                            $.ajax({
                                "url": '{{route("subservices_excel.data")}}',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'SubServices.xls');
                                    tempLink.click();
                                }
                            });
                        }
                    },
                     
                ],

            });

            $('#categories tfoot th').each( function (index) {
                if(index != 8) {
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


      <script src="{{ asset('/adminlte/plugins/datatables/jquery.dataTables.js') }}"></script>

    <script src="{{ asset('adminlte/plugins/datatables/dataTables.bootstrap4.js') }}"></script>



@endsection
