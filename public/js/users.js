$(function () {
    FormSamples.init();
});

var FormSamples = function () {
    return {
        // main function to initiate the module
        init: function () {
            var form2 = $('#frm_user');
            var error2 = $('.alert-warning', form2);
            var success2 = $('.alert-success', form2);
            form2.validate({
                errorElement: 'span', // default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",
                rules: {
                    user_name:"required",
                    user_email:"required"
                },
                messages: {
                    user_name: "Please enter User name",
                    user_email: "Please enter User Email"

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
                    var poData = jQuery(document.forms['frm_user']).serializeArray();
                    for (var i=0; i<poData.length; i++)
                        formdata.append(poData[i].name, poData[i].value);
                    if($('#detail_about').length > 0) {
                        var desc = CKEDITOR.instances.detail_about.getData();
                        formdata.append('detail_about', desc);
                    }
                    if($('#user_image').length > 0) {
                        var imgfile1 = document.getElementById("user_image");
                        formdata.append('user_image', $('input[id="user_image"]')[0].files[0]);
                    }
                    $.ajax({
                        type : "POST",
                        url:  $('#frm_user').attr('action'),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data : formdata,
                        processData: false,
                        contentType: false,
                        success : function (data) {
                            swal({
                                position: 'top-end',
                                title: "Success!",
                                text: "The User Profile has been saved",
                                type: "success",
                                showConfirmButton: false,
                                timer: 1500,

                            });

                            $('div.setup-panel div a[href^="#step"]').removeClass('disabled');
                            $('div.setup-panel div a[href="#step-2"]').removeClass("disabled");
                            $('#step-1').closest(".setup-content"),
                                nextStepWizard = $('div.setup-panel div a[href="#step-1"]').parent().next().children("a"),
                                nextStepWizard.removeAttr('disabled').trigger('click');
                            $('div.setup-panel div a[href="#step-2"]').addClass("active");
                            $('.id').val(data.get_id);
                            $('#submit1').hide();
                            $('#next1').show();
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

function loadCity($cityid) {
    var $state_id = $('#state_id').val(); 
    if($state_id>0) {
        $.ajax({
            type : "POST",
            url:  $('#loadCityURL').val(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data : {state_id:$state_id, city_id:$cityid},
            success : function (data) {
                $('#city_id').html(data);
            },
            fail:function (data) {
                swal("Oops!", "Sorry,Could not process your request", "warning");
            }
        });
    }
}

function submitdetails() { 
    submitDetails.init();
}

var submitDetails = function () { 
    return {
        // main function to initiate the module
        init: function () {
            var form2 = $('#main_form2');
            var error2 = $('.alert-warning', form2);
            var success2 = $('.alert-success', form2);
            form2.validate({
                errorElement: 'span', // default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: [],
                rules: {
                    detail_about:"required"
                },
                messages: {
                    detail_about: "Please enter About Trainer"

                },
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                },
                invalidHandler: function (event, validator) {
                    // success2.hide();
                    error2.show();
                    scrollTo(error2, -200);
                },
                highlight: function (element) {  // hightlight error inputs
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
                    var poData = jQuery(document.forms['main_form2']).serializeArray();
                    for (var i=0; i<poData.length; i++)
                        formdata.append(poData[i].name, poData[i].value);
                    var desc = CKEDITOR.instances.detail_about.getData();
                    formdata.append('detail_about', desc);
                    $.ajax({
                        type : "POST",
                        url:  $('#main_form2').attr('action'),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data : formdata,
                        processData: false,
                        contentType: false,
                        success : function (data) {
                            swal({
                                position: 'top-end',
                                title: "Success!",
                                text: "The User Profile has been saved",
                                type: "success",
                                showConfirmButton: false,
                                timer: 1500,

                            });
console.log('qq');
                            $('div.setup-panel div a[href^="#step"]').removeClass('disabled');
                            $('div.setup-panel div a[href="#step-2"]').removeClass("disabled");
                            $('#step-1').closest(".setup-content"),
                                nextStepWizard = $('div.setup-panel div a[href="#step-1"]').parent().next().children("a"),
                                nextStepWizard.removeAttr('disabled').trigger('click');
                            $('div.setup-panel div a[href="#step-2"]').addClass("active");
                            $('.id').val(data.get_id);
                            $('#submit1').hide();
                            $('#next1').show();
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


$(document).ready(function () {

    var navListItems = $('div.setup-panel div a'),
        allWells = $('.setup-content'),
        allNextBtn = $('.nextBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
            $item = $(this);

        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-success').addClass('btn-default');
            $item.removeClass('btn-default').addClass('btn-success');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });

    allNextBtn.click(function () {
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='url']"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for (var i = 0; i < curInputs.length; i++) {
            if (!curInputs[i].validity.valid) {
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if (isValid) nextStepWizard.removeAttr('disabled').trigger('click');
    });

    $('div.setup-panel div a.btn-success').trigger('click');

});

