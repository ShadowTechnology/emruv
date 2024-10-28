

//var filterType = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
var filterType = /^(?:image\/bmp|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/tiff|image\/x\-portable\-bitmap|image\/x\-xbitmap|image\/x\-xpixmap)$/i;



var uploadFile; var $imgid;

var loadImageFile = function ($imgid, $imgnameid, $allowpdf) {
  var uploadImage = document.getElementById($imgid);
  
  //check and retuns the length of uploded file.
  if (uploadImage.files.length === 0) { 
    return; 
  }
  
  //Is Used for validate a valid file.
  let uploadFile = document.getElementById($imgid).files[0];  
  let filesize = uploadFile.size;

  var fileinkb = filesize / 1024;

  if($allowpdf == 'allowpdf') {
    filterType = /^(?:image\/bmp|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/tiff|image\/x\-portable\-bitmap|image\/x\-xbitmap|image\/x\-xpixmap|application\/pdf)$/i;
  } else {
    filterType = /^(?:image\/bmp|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/tiff|image\/x\-portable\-bitmap|image\/x\-xbitmap|image\/x\-xpixmap)$/i;
  }
 
  if (!filterType.test(uploadFile.type)) {
    swal('Oops',"Please select a valid image",'warning');
    $('#'+$imgid).val('');
    return;
  }
console.log(fileinkb);

  if (fileinkb >=1000) {
    swal('Oops',"Please select an image of size < 1 MB",'warning');
    $('#'+$imgid).val('');
    return;
  }

  $imgid = $imgid;
  var fileReader = new FileReader();
  fileReader.readAsDataURL(uploadFile);

  fileReader.onload = function (event) {
  var image = new Image();
  
  image.onload=function(){
    //  document.getElementById("original-Img").src=image.src;
     if(fileinkb >=1000) {
    /*swal('Oops',"Please select the file less than 1 MB",'warning'); return false;
  }*/
      var canvas=document.createElement("canvas");
      var context=canvas.getContext("2d");

      var image_type = $('#image_upload_type').val();
      console.log(image_type);//  image_type = 'DEFAULT';
      if(typeof image_type != undefined) {
        switch(image_type) {
          case 'GETSTARTED':
            canvas.width=1200;
            canvas.height=2595;
          break;

          case 'HOME':
            canvas.width=800;
            canvas.height=450;
          break;
          
          case 'RESTAURANT':
            canvas.width=800;
            canvas.height=450;
          break;
          
          case 'COUPON':
            canvas.width=800;
            canvas.height=450;
          break;

          case 'COVER':
            canvas.width=800;
            canvas.height=450;
          break;

          case 'LOGO':
            canvas.width=500;
            canvas.height=500;
          break;
          
          default:
            canvas.width=image.width/4;
            canvas.height=image.height/4;
          break; 
        }
      }
      console.log("pp"+canvas.width);
      console.log("pp"+canvas.height);
    } else {
      var canvas=document.createElement("canvas");
      var context=canvas.getContext("2d");
      canvas.width=image.width;
      canvas.height=image.height;

      var image_type = $('#image_upload_type').val();
      console.log(image_type);//  image_type = 'DEFAULT';
      if(typeof image_type != undefined) {
        switch(image_type) {
          case 'LOGO':
          if(image.width >500) {
            canvas.width=500;
          }
          if(image.height>500) {
            canvas.height=500;
          }
          break;
          case 'COVER':
          if(image.width >800) {
            canvas.width=800;
          }
          if(image.height>450) {
            canvas.height=450;
          }
          break;
        }
      }
      console.log("qq"+canvas.width);
      console.log("qq"+canvas.height);
    }

      
      
      context.drawImage(image,
          0,
          0,
          image.width,
          image.height,
          0,
          0,
          canvas.width,
          canvas.height
      );
     // document.getElementById("upload-Preview").val(canvas.toDataURL());
      //document.getElementById("upload-Preview").src = canvas.toDataURL();
      dataurl  = canvas.toDataURL();
      return upload(dataurl, $imgnameid);
 
  }
  image.src=event.target.result;   
};
}

function upload(file, $imgnameid) {
    var form = new FormData();
    form.append('image', file);      
    $.ajax({
          url: $('#uploadImageURL').val(),
          data: form,
          processData: false,
          contentType: false,
          type: 'POST',
          headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
          success: function(data){
            $hiddenname = $('#image_upload_name').val();
            $hiddenname = $imgnameid;  console.log($imgnameid);
            $('#'+$hiddenname).val(data);
          }
     });
}


