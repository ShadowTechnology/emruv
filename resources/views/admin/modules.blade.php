@extends('layouts.admin_master') 
@section('accesssettings', 'active')
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
					<h4 class="card-title">
						Modules
						@if($rights['rights']['add'] == 1)
						<a href="#" data-toggle="modal" data-target="#smallModal"><button
								class="btn btn-primary" style="float: right;">Add</button></a>
						@endif
					</h4>

				</div>
				@if($rights['rights']['list'] == 1)
				<div class="card-content collapse show">
					<div class="card-body card-dashboard">
						<div style="width: 100%; overflow-x: scroll; padding-left: -10px;">
							<div class="table-responsicve">
								<table class="table table-striped table-bordered tblcountries">		
									<thead>
										<tr>
											<th>Name</th>	
											<th>Parent Module</th>	
											<th>Rank</th>		
										    <th>Url</th>
										    <th>Icon</th>
											<th>Status</th>
											<th class="not-export-column">Action</th>
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
@if($rights['rights']['add'] == 1)
<div class="modal fade in" id="smallModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="smallModalLabel">Add Module</h4>
			</div>

			<form id="style-form" enctype="multipart/form-data"
				action="{{url('/admin/save/module')}}" method="post">

				{{csrf_field()}}

				<div class="modal-body">
					<div class="row">

						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Module Name</label>
							<div class="form-line">
								<input type="text" class="form-control" name="name" required>
							</div>
						</div>
						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Parent Module</label>
							<div class="form-line">
								<select style="width: 100%" class="form-control select2"
									name="module_id">
									<option value="0">Select Module</option>
									@if(count($module)>0) @foreach($module as $k=>$v)
									<option value="{{$v->id}}">{{$v->module_name}}</option>
									@endforeach @endif
								</select>
							</div>
						</div>
						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Rank</label>
							<div class="form-line">
								<input type="text" class="form-control" name="rank"
									required>
							</div>
						</div>
						
						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Url</label>
							<div class="form-line">
								<input type="text" class="form-control" name="url" required>
							</div>
						</div>

						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Icon</label>
							<div class="form-line">
								<input type="text" class="form-control" name="icon">
							</div>
						</div>
						

						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Status</label>
							<div class="form-line">
								<select class="form-control" name="status" required>
									<option value="1">ACTIVE</option>
									<option value="2">INACTIVE</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="sumbit" class="btn btn-link waves-effect"
						id="add_style">SAVE</button>
					<button type="button" class="btn btn-link waves-effect"
						data-dismiss="modal">CLOSE</button>
				</div>

			</form>
		</div>
	</div>
</div>
@endif
@if($rights['rights']['edit'] == 1)
<div class="modal fade in" id="smallModal-2" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="smallModalLabel">Edit Module</h4>
			</div>

			<form id="edit-style-form" enctype="multipart/form-data"
				action="{{url('/admin/save/module')}}" method="post">

				{{csrf_field()}} <input type="hidden" name="id" id="id">
				<div class="modal-body">
					<div class="row">
						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Name</label>
							<div class="form-line">
								<input type="text" class="form-control" name="name"
									id="edit_name" required>
							</div>
						</div>
						
						
						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Parent Module</label>
							<div class="form-line">
								<select style="width: 100%" class="form-control select2"
									name="module_id" id="edit_module_id">
									<option value="0">Select Module</option>
									@if(count($module)>0) @foreach($module as $k=>$v)
									<option value="{{$v->id}}">{{$v->module_name}}</option>
									@endforeach @endif
								</select>
							</div>
						</div>

						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Rank</label>
							<div class="form-line">
								<input type="text" class="form-control" name="rank"
									id="edit_rank" required>
							</div>
						</div>
						
						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">URL</label>
							<div class="form-line">
								<input type="text" class="form-control" name="url"
									id="edit_url" required>
							</div>
						</div>
						
							<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Icon</label>
							<div class="form-line">
								<input type="text" class="form-control" name="icon"
									id="edit_icon">
							</div>
						</div>
						

						


						<div class="form-group form-float float-left col-md-6">
							<label class="form-label">Status</label>
							<div class="form-line">
								<select class="form-control" name="status" id="edit_status"
									required>
									<option value="1">ACTIVE</option>
									<option value="2">INACTIVE</option>
								</select>
							</div>
						</div>

					</div>
				</div>
				<div class="modal-footer">
					<button type="sumbit" class="btn btn-link waves-effect"
						id="edit_style">SAVE</button>
					<button type="button" class="btn btn-link waves-effect"
						data-dismiss="modal">CLOSE</button>
				</div>

			</form>
		</div>
	</div>
</div>
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
                stateSave: true,
                "ajax": {
                    "url": '{{route("modules.data")}}',
                },
                columns: [
                	{ data: 'module_name',name:'em_modules.module_name'},        
                    { data: 'is_parent_module',name:'pf.module_name'},         
                    { data: 'menu_rank',name:'em_modules.menu_rank'},                   
                    { data: 'url',name:'em_modules.url'},      
                    { data: 'icon',name:'em_modules.icon'},                 
                    { data: 'is_status',name:'em_modules.is_status'},
                    {
                        data:null,
                        "render": function ( data, type, row, meta ) {

                            var tid = data.id;
                            @if($rights['rights']['edit'] == 1)
                            	return '<a href="#" onclick="loadModule('+tid+')" title="Edit "><i class="ft-edit"></i></a>';
                        	@else 
                                return '';
                            @endif
                        },

                    },
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }, 
                    { "orderable": false, "targets": 5 } 
                ]

            });

            $('.tblcountries tfoot th').each( function () {
                var title = $(this).text();
                var index=$(this).index();
                if(index<5){
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
            @if($rights['rights']['edit'] == 1)
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

                           swal('Success',response.message,'success');

                           $('.tblcountries').DataTable().ajax.reload();

                           $('#smallModal-2').modal('hide');

                        }
                        else if (response.status == "FAILED") {

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

            @endif

        });

        function loadModule(id){
            $("#edit-style-form")[0].reset();
            var request = $.ajax({
                type: 'post',
                url: " {{URL::to('admin/edit/module')}}",
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
                $('#edit_name').val(response.data.module_name);  
                $('#edit_url').val(response.data.url);  
                $('#edit_icon').val(response.data.icon);  
                $('#edit_rank').val(response.data.menu_rank);   
                $('#edit_module_id').val(response.data.parent_module_fk).trigger('change');           
                $('#edit_status').val(response.data.status);               
                $('#smallModal-2').modal('show');

            });
            request.fail(function (jqXHR, textStatus) {

                swal("Oops!", "Sorry,Could not process your request", "error");
            });
        }


    </script>

@endsection
