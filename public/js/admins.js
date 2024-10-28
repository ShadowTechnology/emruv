$(function () {
    FormSamples.init();  
});
var FormSamples = function () {
    return {
        // main function to initiate the module
        init: function () {
            var form2 = $('#frm_admins');
            var error2 = $('.alert-warning', form2);
            var success2 = $('.alert-success', form2);
            form2.validate({
                errorElement: 'span', // default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",
                rules: {
                    user_name:"required",
                    user_email:"required",
                    password:"required",
                    confirm_password: {
                        equalTo: "#password"
                    },
                    user_type:"required",
                },
                messages: {
                    user_name:"Please Enter the Admin User Name",
                    user_email:"Please Enter the Admin User Email",
                    password:"Please Enter the Admin User Password",
                    confirm_password:"Please Re-Enter the Password same as Password",
                    user_type:"Please Select the User Role",

                },
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                },
                invalidHandler: function (event, validator) {
                    // success2.hide();
                    error2.show();
                    scrollTo(error2, -200);
                },
                highlight: function (element) {	 // hightlight error inputs
                    $(element)
                        .closest('.control-group').removeClass('success').addClass('error');
                },
                unhighlight: function (element) { // revert the change dony by
                    // hightlight
                    $(element)
                        .closest('.control-group').removeClass('error');
                },
                success: function (label) {
                    // display success icon for other inputs
                    label
                        .addClass('valid').addClass('help-inline ')
                        .closest('.control-group').removeClass('error').addClass('success');
                },
                submitHandler: function (form) {
                    var formdata = new FormData();
                    var poData = jQuery(document.forms['frm_admins']).serializeArray();
                    for (var i=0; i<poData.length; i++)
                        formdata.append(poData[i].name, poData[i].value);
                    var imgfile1 = document.getElementById("user_image");
                    formdata.append('user_image', $('input[id="user_image"]')[0].files[0]);
                    $.ajax({
                        type : "POST",
                        url:  $('#frm_admins').attr('action'),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data : formdata,
                        processData: false,
                        contentType: false,
                        success : function (data) {
                            if(data.status == 'Failure')   {
                                swal("Oops!", data.message, "warning");
                            } else {
                                swal({
                                    position: 'top-end',
                                    title: "Success!",
                                    text: "The Admin has been saved",
                                    type: "success",
                                    showConfirmButton: false,
                                    timer: 1500,

                                });
                                location.href = $('#admin_url').val();
                            }
                            
                        },
                        fail:function (data) {
                            swal("Oops!", "Sorry,Could not process your request", "warning");
                        }
                    });
                }
            });
        }
    };
}();

function deleteAdmin($id, $obj) {
    if(confirm("Are you sure to Delete?")) {
        $.ajax({
                type : "POST",
                url:  $($obj).data('deleteurl'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data : {id:$id},
                dataType:'JSON',
                success : function (data) { 
                    location.reload();
                    swal({
                        position: 'top-end',
                        title: "Success!",
                        text: "The Admin has been saved",
                        type: "success",
                        showConfirmButton: false,
                        timer: 1500,

                    });
                },
                fail:function (data) {
                    swal("Oops!", "Sorry,Could not process your request", "warning");
                }
            });
          
    }
            
}