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
                            Services
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="{!! url('admin/service/add') !!}"><button class="btn btn-primary" style="float: right;">Add</button></a>
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="services" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Sub Category</th>
                                        <th>Name</th>
                                        <th>Service Based On</th>
                                        <th>Display Text</th>
                                        <th>Position</th>
                                        <th>Image</th>
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
            var table = $('#services').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "ajax": {
                    "url": '{{route("services.data")}}',
                },
                columns: [
                    { data: 'category_name', name: 'em_category.name'},
                    { data: 'sub_category_name', name: 'em_sub_category.name'},
                    { data: 'name', name: 'em_sub_cat_services.name'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var basedon = data.service_based_on;
                            if(basedon == 1) return ' Hourly Based';
                            else if(basedon == 2) return ' Fixed Price';
                        },

                    },
                    { data: 'display_text', name:'em_sub_cat_services.display_text'},
                    { data: 'position', name:'em_sub_cat_services.position'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var imagesrc = data.is_image;

                            return '<img src="'+imagesrc+'" width=40 height=40 />';
                        },

                    },
                    
                    { data: 'status', name:'em_sub_cat_services.status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="{{URL::to('admin/service/edit')}}/'+tid+'"><i class="ft-edit"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 8 },
                    { "orderable": false, "targets": 6 },
                    { "orderable": false, "targets": 3 }
                ],
                dom: 'Blfrtip',
                buttons: [ 
                    { 
          
                        extend: 'excel',
                        text: 'Export Excel',
                        className: 'btn btn-warning btn-md ml-3',
                        action: function (e, dt, node, config) {
                            $.ajax({
                                "url": '{{route("services_excel.data")}}',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'Services.xls');
                                    tempLink.click();
                                }
                            });
                        }
                    },
                     
                ],

            });

            $('#services tfoot th').each( function (index) {
                if(index != 8 && index != 6 && index != 3) {
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
