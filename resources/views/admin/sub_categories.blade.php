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
                             SUB CATEGORIES
                        </h2>
                        @if($rights['rights']['add'] == 1)
                        <a href="{!! url('admin/subcategory/add') !!}"><button class="btn btn-primary" style="float: right;">Add</button></a>
                        @endif
                    </div>
                    @if($rights['rights']['list'] == 1)
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categories" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Name</th>
                                        <th>Commission %</th>
                                        <th>Position</th>
                                        <th>Image</th>
                                        <th>Rate Card</th>
                                        <th>Video Link</th>
                                        <!-- <th>Home Display</th> -->
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                  <tr><th></th><th></th><th></th>
                                      <th></th><th></th><th></th>
                                      <th></th><!-- <th></th> --><th></th>
                                      <th></th>
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
                    "url": '{{route("subcategories.data")}}',
                },
                columns: [
                    { data: 'category_name', name: 'em_category.name'},
                    { data: 'name', name: 'em_sub_category.name'},
                    { data: 'commission_percentage', name:'em_sub_category.commission_percentage'},
                    { data: 'position', name:'em_sub_category.position'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var imagesrc = data.is_image;

                            return '<img src="'+imagesrc+'" width=40 height=40 />';
                        },

                    },
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var ratecard = data.ratecard;
                            if(ratecard != null)
                                return '<a href="{{URL('/')}}/public/uploads/categories/'+ratecard+'" target="_blank">View Rate card </a>';
                            else 
                                return '';
                            
                        },

                    },
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var video_link = data.video_link;
                            if(video_link != null)
                                return '<a href="'+video_link+'" target="_blank">View Video Link </a>';
                            else 
                                return '';
                            
                        },

                    },
                    /*{ data: 'home_display'},*/
                    { data: 'status', name:'em_sub_category.status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            return '<a href="{{URL::to('admin/subcategory/edit')}}/'+tid+'"><i class="ft-edit"></i></a>&nbsp;<a href="{{URL::to('admin/subcategory/instructions')}}/'+tid+'"><i class="ft-book"></i></a>';
                            @else 
                            return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 4 },
                    { "orderable": false, "targets": 5 },
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
                                "url": '{{route("subcategories_excel.data")}}',
                                "data": dt.ajax.params(),
                                "type": 'get',
                                "success": function(res, status, xhr) {
                                    var csvData = new Blob([res], {type: 'text/xls;charset=utf-8;'});
                                    var csvURL = window.URL.createObjectURL(csvData);
                                    var tempLink = document.createElement('a');
                                    tempLink.href = csvURL;
                                    tempLink.setAttribute('download', 'SubCategories.xls');
                                    tempLink.click();
                                }
                            });
                        }
                    },
                     
                ],
            });

            $('#categories tfoot th').each( function (index) {
                if(index != 4 && index != 5 && index != 8) {
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
