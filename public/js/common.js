$( ".googleaddress" ).autocomplete({
    source: function( request, response ) {
        $.ajax({
          url: $('#getPlacesURL').val(),
          method:"POST",
          dataType: "json",
          data: {
            term: request.term
          },
          headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
          success: function( data ) {
               var arr = [];
               var i = 0;
               var fullObj = data;
               $.each(data, function(index, value){
                   $.each(value, function(idx, v){
                       var obj = {
                           label: value['description'],
                           value: value['description'],
                           place_id: v
                       };
                       if(idx == "place_id"){
                           arr[i] = obj;
                           i++;
                       }
                   });
               });
               response(arr);
           }
       });
    },
    minLength: 2,
    select: function( event, ui ) {
     
        $.ajax({
              url: $('#getPlaceDetailURL').val(),
              method:"POST",
              dataType: "json",
              data: {
                place_id: ui.item.place_id
              },
              headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
              success: function( data ) {
                $('#latitude').val(data['latitude']);
                $('#longitude').val(data['longitude']);
              }
        });
    },
});