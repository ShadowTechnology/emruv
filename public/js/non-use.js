
$(function () {
    FormSamples2.init();
});
var FormSamples2 = function () {
    return {
        // main function to initiate the module
        init: function () {
            var form2 = $('#main_form2');
            var error2 = $('.alert-warning', form2);
            var success2 = $('.alert-success', form2);
            $('#submit2').show();
            $('#next2').hide();
            form2.validate({
                errorElement: 'span', // default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",
                rules: {
                    department_id_get: "required",
                    course_id_get: "required",
                    credential: "required",
                    subject_id_get: "required",
                    title: "required",
                    medium: "required",
                    duration_1: "required",
                    duration_2: "required",
                    eligibility: "required",
                    mode: "required",
                    entry_mode: "required",
                    test_exam: "required",
                    fees_structure: "required",
                    no_of_seat: "required",
                    twinning_program: "required",
                    course_description: "required",
                    course_path_guidance: "required",

                },
                messages: {
                    department_id_get: "Please enter your Department select",
                    course_id_get: "Please enter your Course select",
                    credential: "Please enter your credential",
                    subject_id_get: "Please enter your subject select",
                    title: "Please enter your title",
                    medium: "Please enter your medium",
                    duration_1: "Please enter your duration of Year",
                    duration_2: "Please enter your duration of Month",
                    eligibility: "Please enter your eligibility",
                    mode: "Please enter your mode",
                    entry_mode: "Please enter your entry mode",
                    test_exam: "Please enter your test exam",
                    fees_structure: "Please enter your fees structure",
                    no_of_seat: "Please enter your no of seat",
                    twinning_program: "Please enter your twinning program",
                    course_description: "Please enter your course description",
                    course_path_guidance: "Please enter your course path guidance",
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
                    var poData = jQuery(document.forms['main_form2']).serializeArray();
                    for (var i=0; i<poData.length; i++)
                        formdata.append(poData[i].name, poData[i].value);
                    $.ajax({
                        type : "POST",
                        url: $('#main_form2').attr('action'),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data : formdata,
                        processData: false,
                        contentType: false,
                        success : function (data) {
                            $('#get_id3').val(data.get_id);
                            success2.hide();
                            error2.hide();
                            swal({
                                position: 'top-end',
                                title: "Success!",
                                text: "The Courses has been added",
                                type: "success",
                                showConfirmButton: false,
                                timer: 1500,

                            });
                            $('div.setup-panel div a[href="#step-3"]').removeClass("disabled");
                            $('#step-2').closest(".setup-content"),
                                nextStepWizard = $('div.setup-panel div a[href="#step-2"]').parent().next().children("a"),
                                nextStepWizard.removeAttr('disabled').trigger('click');
                            $('div.setup-panel div a[href="#step-3"]').addClass("active");
                            $('#submit2').hide();
                            $('#next2').show();
                            // scrollTo($(".alert-warning"), 0);
                        },
                        fail:function (data) {
                            swal("Oops!", "Sorry,Could not process your request", "warning");
                            $('#submit2').show();
                            $('#next2').hide();
                        }
                    });
                }
            });
        }
    };
}();



/////////wizard step
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


















//////////////////////  Not in use
function MainInfo(){

    var college_name = $('#college_name').val();
    var college_code = $('#college_code').val();
    var college_url = $('#college_url').val();
    var rank = $('#rank').val();
    var ranked_by = $('#ranked_by').val();
    var owned_by_sector = $('#owned_by_sector').val();
    var phone_number = $('#phone_number').val();
    var gender = $('#gender').val();
    var email = $('#email').val();
    var city = $('#city').val();
    var state = $('#state').val();
    var country = $('#country').val();
    var address = $('#address').val();
    var pin_code = $('#pin_code').val();
    var fax_number = $('#fax_number').val();
    var website = $('#website').val();
    var social_link = $('#social_link').val();
    var vedio_link = $('#vedio_link').val();
    var group_name = $('#group_name').val();
    var approved_by = $('#approved_by').val();
    var affiliated_to = $('#affiliated_to').val();
    var accreditation = $('#accreditation').val();
    var latitude = $('#latitude').val();
    var longitude = $('#longitude').val();
    var status = $('#status').val();


    if(college_name =='' || college_code =='' || college_url =='' || rank ==''|| ranked_by ==''|| owned_by_sector ==''|| phone_number ==''
        ||  gender ==''|| email ==''|| city ==''|| state ==''|| country ==''|| address ==''|| pin_code ==''|| fax_number ==''|| website ==''|| social_link ==''
        || vedio_link ==''|| group_name ==''|| approved_by ==''|| affiliated_to ==''|| accreditation ==''|| latitude ==''|| longitude ==''|| status =='')
        return false;


    var request = $.ajax({
        type: 'post',
        url: " {{URL::to('/admin/manage_college_detail_insert')}}",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data:{
            college_name:college_name,
            college_code:college_code,
            college_url:college_url,
            rank:rank,
            ranked_by:ranked_by,
            owned_by_sector:owned_by_sector,
            phone_number:phone_number,
            gender:gender,
            email:email,
            city:city,
            state:state,
            country:country,
            address:address,
            pin_code:pin_code,
            fax_number:fax_number,
            website:website,
            social_link:social_link,
            vedio_link:vedio_link,
            group_name:group_name,
            approved_by:approved_by,
            affiliated_to:affiliated_to,
            accreditation:accreditation,
            latitude:latitude,
            longitude:longitude,
            status:status,
        },
        dataType:'json',
        encode: true
    });
    request.done(function (response) {
        $('#get_id').val(response.get_id);
        swal({
            title: "Success!",
            text: "The New Data has been added",
            type: "success",

        });
    });
    request.fail(function (jqXHR, textStatus) {

        swal("Oops!", "Sorry,Could not process your request", "warning");
    });
}



function UpdateInfo(){

    var document_name = $('#document_name').val();
    var document_path = $('#document_path').val();
    var document_type = $('#document_type').val();
    var admission_section = $('#admission_section').val();
    var admission_content = $('#admission_content').val();
    var about_section = $('#about_section').val();
    var about_content = $('#about_content').val();
    var result_section = $('#result_section').val();
    var result_content = $('#result_content').val();
    var max_salary = $('#max_salary').val();
    var avg_salary = $('#avg_salary').val();
    var min_salary = $('#min_salary').val();
    var list_of_company = $('#list_of_company').val();
    var facility_type = $('#facility_type').val();
    var faq_question = $('#faq_question').val();
    var faq_answer = $('#faq_answer').val();
    var review_content = $('#review_content').val();
    var review_no_of_star = $('#review_no_of_star').val();
    var feature_type = $('#feature_type').val();
    var id = $("#get_id").val();

    var request = $.ajax({
        type: 'post',
        url: " {{URL::to('/admin/manage_college_detail_update')}}",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data:{
            document_name:document_name,
            document_path:document_path,
            document_type:document_type,
            admission_section:admission_section,
            admission_content:admission_content,
            about_section:about_section,
            about_content:about_content,
            result_section:result_section,
            result_content:result_content,
            max_salary:max_salary,
            avg_salary:avg_salary,
            min_salary:min_salary,
            list_of_company:list_of_company,
            facility_type:facility_type,
            faq_question:faq_question,
            faq_answer:faq_answer,
            review_content:review_content,
            review_no_of_star:review_no_of_star,
            feature_type:feature_type,
            id:id
        },
        dataType:'json',
        encode: true
    });
    request.done(function (response) {

        swal({
            title: "Success!",
            text: "The Information has been updated",
            type: "success",

        });

    });
    request.fail(function (jqXHR, textStatus) {

        swal("Oops!", "Sorry,Could not process your request", "warning");
    });
}
