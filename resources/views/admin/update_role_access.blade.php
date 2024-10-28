@extends('layouts.admin_master')
@section('accesssettings', 'active')
@section('content')
<?php
use App\Module;
use App\RoleModuleMapping;

use App\Http\Controllers\AdminController;

$rights = AdminController::getRights(); 
?>
<meta name="csrf-token" content="{{ csrf_token() }}">

@if($rights['rights']['view'] == 1)
<!-- <link href="{{url('public/css/treeview.css')}}" rel="stylesheet"> -->
<section class="content">
	<!-- Exportable Table -->
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title">Role Mapping</h4>

				</div>

				<form id="edit-style-form" enctype="multipart/form-data"
					action="{{url('/admin/save/role_access')}}" method="post">
                    {{csrf_field()}}
					 <input type="hidden" name="id" id="id"
						value="<?php //echo $subcategory->id;?>">
					<div class="modal-body">
					<div class="row col-md-12">
					<div class="col-md-12">
					<h3><?php echo $role_name;?></h3>
					</div>
					<input type="hidden" name="role_fk" id="role_fk" value="<?php echo $role_fk;?>">
					</div>
						<div class="row">
						
						
						
						<?php
    $update_id = 1;

    function module_tree($index, $update_id)
    {
        $module = Module::where("parent_module_fk", $index)->get();

        if ($index == "-1") {
            echo '<ul id="tree1" class="treeview" >';
        } else {
            echo '<ul>';
        }

        foreach ($module as $mc) {
            $id = $mc['id'];
            $name = $mc['module_name'];
            $menu_item = $mc['menu_item'];
            $parent = $mc['parent_module_fk'];
            $url = $mc['url'];

            $module_add = $mc['module_add'];
            $module_edit = $mc['module_edit'];
            $module_delete = $mc['module_delete'];
            $module_view = $mc['module_view'];
            $module_list = $mc['module_list'];
            $module_status_update = $mc['module_status_update'];
            $module_aadhar_status_update=$mc['module_aadhar_status_update'];

            echo "<input type='hidden' name='SysroleModule[module_fk][]' value=" . $id . "/>";
            // echo CHtml::hiddenField('SysroleModule[module_fk][]',$id,array('id'=>'module_fk'.$id));
            $class = "";
            $type = 0;
            if ($url != "" && $url != "#") {
                $type = 1;
                $class = "";
            }
            $checked = "";
            $achecked = "";
            $echecked = "";
            $dchecked = "";
            $vchecked = "";
            $apchecked = "";
            $lchecked='';
            $apaschecked='';
            if (! empty($update_id)) {

                // $qry = "select `ra_add`,`ra_edit`,`ra_delete`,`ra_view` from role_access where ra_role_fk=$update_id and ra_module_fk=$id ";
                // $counts_select = mysql_query ( $qry );
                // $counts = mysql_fetch_array ( $counts_select );

                $role_access = RoleModuleMapping::where("ra_role_fk", $update_id)->where("ra_module_fk", $id)->first();
                

                if (!empty($role_access)) {
                    $checked = "checked";
                    if ($role_access->ra_add == 1) {
                        $achecked = "checked";
                    }
                    if ($role_access->ra_edit == 1) {
                        $echecked = "checked";
                    }
                    if ($role_access->ra_delete == 1) {
                        $dchecked = "checked";
                    }
                    if ($role_access->ra_view == 1) {
                        $vchecked = "checked";
                    }
                    if ($role_access->ra_list == 1) {
                        $lchecked = "checked";
                    }
                    if ($role_access->ra_status_update == 1) {
                        $apchecked = "checked";
                    }
                    if ($role_access->ra_aadhar_status_update == 1) {
                        $apaschecked = "checked";
                    }
                    
                }
            }
            echo '<li class="treeview-menu">';
            ?>
<?php if($url!='#'){?>
											
									<div onMouseOver="view('<?php echo $id; ?>')"
								onmouseout="view_hidden('<?php echo $id; ?>')">
								<input type="checkbox" name="SysroleModule[id][]"
									id="SysroleModule_ids<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $checked; ?> onclick="checkparent(<?php echo $parent; ?>,<?php echo $id;?>)">
								<div style="margin-top: -27px !important; margin-left: 20px;">
												<?php echo $name;?>
												</div>



								<b id="action<?php echo $id;?>"
									style="display: none; margin-left: 20px;"> 
									<?php if($module_add==1){?>		
									 <input style="margin-top: -4px;" type="checkbox"
									name="SysroleModule_add<?php echo $id; ?>"
									id="SysroleModule_add<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $achecked; ?> /> Add
									<?php }?>
									<?php if($module_edit==1){?>		
									 <input style="margin-top: -4px;" type="checkbox"
									name="SysroleModule_edit<?php echo $id; ?>"
									id="SysroleModule_edit<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $echecked; ?> /> Edit
									<?php }?>
									<?php if($module_delete==1){?>		
									 <input style="margin-top: -4px;" type="checkbox"
									name="SysroleModule_delete<?php echo $id; ?>"
									id="SysroleModule_delete<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $dchecked; ?> /> Delete
									<?php }?>
									<?php if($module_view==1){?>		
									 <input style="margin-top: -4px;" type="checkbox"
									name="SysroleModule_view<?php echo $id; ?>"
									id="SysroleModule_view<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $vchecked; ?> /> View
									<?php }?>
									
									<?php if($module_list==1){?>
									<input style="margin-top: -4px;" type="checkbox"
									name="SysroleModule_list<?php echo $id; ?>"
									id="SysroleModule_list<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $lchecked; ?> /> List
									<?php }?>
									<?php if($module_status_update==1){?>		
									
									<input style="margin-top: -4px;" type="checkbox"
									name="SysroleModule_statusupdate<?php echo $id; ?>"
									id="SysroleModule_statusupdate<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $apchecked; ?> /> Status Update

<?php }?>

<?php if($module_aadhar_status_update==1){?>		
									
									<input style="margin-top: -4px;" type="checkbox"
									name="SysroleModule_aadharstatusupdate<?php echo $id; ?>"
									id="SysroleModule_aadharstatusupdate<?php echo $id;?>"
									value="<?php echo $id; ?>" <?php echo $apaschecked; ?> /> Aadhar Status Update

<?php }?>
								</b>

												<?php } else {?>
												<div>
									<input type="checkbox" name="SysroleModule[id][]"
										id="SysroleModule_ids<?php echo $id;?>"
										value="<?php echo $id; ?>" <?php echo $checked; ?> onclick="checkchild(<?php echo $id; ?>,0);checkparent(<?php echo $id; ?>)">
									<div style="margin-left: 20px; margin-top: -27px;">
												<?php echo $name;?>
												</div>
												<?php }?>
											</div>
											
											<?php
            module_tree($id, $update_id);
            echo '</li>';
        }
        echo '</ul>';
    }
    module_tree(0, $role_fk);
    ?>
<?php
/*
 * ?>
 * <ul id="tree1">
 *
 * @foreach($modules as $module)
 *
 * <li style="cursor: pointer;"><input type="checkbox"> {{
 * $module->module_name }}
 * <br>
 * <span >
 * <input type="checkbox"> List
 * <input type="checkbox"> Add
 * <input type="checkbox"> Edit
 * <input type="checkbox"> View
 * <input type="checkbox"> Status Update
 *
 * </span> @if(count($module->childs))
 *
 * @include('admin/manageChild',['childs' => $module->childs])
 *
 * @endif</li> @endforeach
 *
 * </ul>
 * <?php
 */
?>
						</div>





						</div>
						<div class="modal-footer">
							@if($rights['rights']['edit'] == 1)
							<button type="submit" class="btn btn-link waves-effect"
								id="edit_style">SAVE</button>
							@endif
							<button type="button" class="btn btn-link waves-effect"
								data-dismiss="modal">
								<a href="{{url('/admin/role_module_mapping')}}">BACK</a>
							</button>
						</div>
				
				</form>

			</div>
		</div>
	</div>
</section>


@endif


@endsection @section('scripts')
 <script src="http://3.12.170.234/deputex/public/js/jquery-form.js"></script>
<script>


$(function() {
	$("#edit-style-form").submit(function(e) {

	    e.preventDefault(); // avoid to execute the actual submit of the form.

	    var form = $(this);
	    var url = form.attr('action');
	    
	    $.ajax({
	           type: "POST",
	           url: url,
	           data: form.serialize(), // serializes the form's elements.
	           success: function(response)
	           {

              if (response.status == "SUCCESS") {
                swal('Success',response.message,'success');
                window.location.href="{{url('/admin/role_module_mapping')}}";

             }
             else if (response.status == "FAILED") {

                 swal('Oops',response.message,'warning');

             }
		           
	               //alert(data);
	               // show response from the php script.
	           }
	         });

	    
	});
	
//     $('#edit_style').click(function () {
// //alert("DADA");
//         var options = {

//             beforeSend: function (element) {

//                 $("#edit_style").text('Processing..');

//                 $("#edit_style").prop('disabled', true);

//             },
//             success: function (response) {

//                 $("#edit_style").prop('disabled', false);

//                 $("#edit_style").text('SAVE');

//                 if (response.status == "SUCCESS") {

//                    swal('Success',response.message,'success');


//                    window.location.href="admin/role_access";

//                   // $('.tblcountries').DataTable().ajax.reload();

//                    $('#smallModal-2').modal('hide');

                   

//                 }
//                 else if (response.status == "FAILED") {

//                     swal('Oops',response.message,'warning');

//                 }

//             },
//             error: function (jqXHR, textStatus, errorThrown) {

//                 $("#edit_style").prop('disabled', false);

//                 $("#edit_style").text('SAVE');

//                 swal('Oops','Something went to wrong.','error');

//             }
//         };
//         $("#edit-style-form").ajaxForm(options);
//     });

});
</script>
<script type="text/javascript"
		src="{{url('public/plugins/checkboxtree/library/jquery-1.4.4.js')}}"></script>

<script type="text/javascript"
	src="{{url('public/plugins/checkboxtree/library/jquery-ui-1.8.12.custom/js/jquery-ui-1.8.12.custom.min.js')}}"></script>
<link rel="stylesheet" type="text/css"
	href="{{url('public/plugins/checkboxtree/library/jquery-ui-1.8.12.custom/css/smoothness/jquery-ui-1.8.12.custom.css')}}" />
<script type="text/javascript"
	src="{{url('public/plugins/checkboxtree/jquery.checkboxtree.js')}}"></script>
	
	<link rel="stylesheet" type="text/css"
	href="{{url('public/plugins/checkboxtree/jquery.checkboxtree.css')}}" />


<script type="text/javascript"
	src="{{url('public/plugins/checkboxtree/library/jquery.cookie.js')}}"></script>



<div style="display: none">
	<code class="jquery" lang="text/javascript">
		$('#tree1').checkboxTree(); </code>
</div>

<script>
function view(id){
	    
 	document.getElementById("action"+id).style.display="block";
 }
 function view_hidden(id){
 
 	document.getElementById("action"+id).style.display="none";
 } 
 function checkparent(value,id){
if(document.getElementById("SysroleModule_ids"+id).checked==true){
	document.getElementById("SysroleModule_ids"+value).checked=true; 	
}else{
	//document.getElementById("SysroleModule_ids"+id).checked=false; 	
}
	 
//  	$.ajax({
//  		 url: "",
//  		 type: "POST",
//  		 data:"child_id="+value,
//  		 success: function(text){
//  	 		 alert(text);
//  			 var data=text.split(",");
//  			 var count=parseFloat(data.length)-1;
//  			 for(i=0;i<count;i++){
//  				 document.getElementById("SysroleModule_id"+data[i]).checked=true; 				
//  			 }	 
//  		}
//  	});
 }
 function checkchild(val,type){
 	var bool=0;
 	if(document.getElementById("SysroleModule_ids"+val).checked==true){
 		bool=1;
 	}
 	//aevd(val,bool);
 	if(type==0){    		
 	$.ajax({
 		 url: "../get_modules",
 		 type: "GET",
 		 data:"parent_id="+val,
 		 success: function(text){
 			var results=text.split(",");
 			var count=results.length;
 			for(i=0;i<count;i++){
 				arr_set=parseInt(results[i]);
 				document.getElementById("SysroleModule_ids"+arr_set).checked=bool;
 				//aevd(arr_set,bool);
 			}
 		}
 	});	
 	}
 }
        $(function() {

        	$('.jquery').each(function() {
                eval($(this).html());
            });
      		$('.button').button();
        	
           /* $('#edit_style').on('click', function () {
//alert("DADA");
                var options = {

                    beforeSend: function (element) {

                        $("#edit_style").text('Processing..');

                        $("#edit_style").prop('disabled', true);

                    },
                    success: function (response) {

                        $("#edit_style").prop('disabled', false);

                        $("#edit_style").text('SAVE');

                        if (response.status == "SUCCESS") {

                           swal('Success',response.message,'success');


                           window.location.href('admin/subcategories');

                           $('.tblcountries').DataTable().ajax.reload();

                           $('#smallModal-2').modal('hide');

                           

                        }
                        else if (response.status == "FAILED") {

                            swal('Oops',response.message,'warning');

                        }

                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        $("#edit_style").prop('disabled', false);

                        $("#edit_style").text('SAVE');

                        swal('Oops','Something went to wrong.','error');

                    }
                };
                $("#edit-style-form").ajaxForm(options);
            });*/

        });

      


    </script>

@endsection
