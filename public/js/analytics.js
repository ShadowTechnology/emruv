function loadUsers($option) {
	var request = $.ajax({
        type: 'post',
        url: " {{URL::to('admin/edit/coupons')}}",
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

    });
    request.fail(function (jqXHR, textStatus) {

        swal("Oops!", "Sorry,Could not process your request", "error");
    });
}