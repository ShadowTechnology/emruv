 
<style>
   .btn-file {
   position: relative;
   overflow: hidden;
   }
   .btn-file input[type=file] {
   position: absolute;
   top: 0;
   right: 0;
   min-width: 100%;
   min-height: 100%;
   font-size: 100px;
   a text-align: right;
   filter: alpha(opacity=0);
   opacity: 0;
   outline: none;
   background: white;
   cursor: inherit;
   display: block;
   }
   #img-upload {
   width: 90px;
   height: 100px !important;
   object-fit: cover;
   border-radius: 73px;
   }
</style>
<link rel="stylesheet" href="https://code.getmdl.io/1.1.3/material.orange-indigo.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<script defer src="https://code.getmdl.io/1.1.3/material.min.js"></script>
<section class="hero_in general">
   <div class="wrapper">
      <div class="container">
         <h1 class="fadeInUp"><span></span>Sign Up</h1>
         <input type="hidden" name="reg_id" id="reg_id"> 
      </div>
   </div>
</section>
<!--/hero_in-->
<div id="sign-in-dialog" class=" " style="border: ridge;">
   <div class="small-dialog-header">
      <h3>Sign Up</h3>
   </div>
   <form id="sign-in-form" name="sign-in-form" action="#"  method="post" enctype="multipart/form-data">
      <div class="form-group">
         <label>Your Name</label>
         <input class="form-control" type="text" name="name" id="name"> <i class="ti-user"></i> 
      </div>
      <span class="qc" id="name_c" style="display:none"></span>
      <div class="form-group">
         <label>Your Email</label>
         <input class="form-control" type="email" name="email" id="email"> <i class="icon_mail_alt"></i> 
      </div>
      <span class="qc" id="email_c" style="display:none"></span>
      <div class="form-group input-group">
         <div class="input-group-prepend" style="width: 29%;">
            <select name="countrycode" id="countrycode" class="form-control" autocomplete="off">
               @foreach($countries as $countrieslist)
               <option data-countrycode="{{$countrieslist->name}}" value="{{$countrieslist->id}}" <?php if($countrieslist->name=='44'){ echo 'selected'; } ?>>+ {{$countrieslist->phonecode}}</option>
               @endforeach 
            </select>
         </div>
       <!--   <input name="phone-number" id="phone-number" class="form-control" placeholder="Enter your mobile number" type="tel" maxlength="11" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" minlength="11">  -->
        <input name="mobile" id="phone-number" class="form-control" placeholder="Enter your mobile number" type="text" > 
      </div>
      <span class="qc" id="mobile_c" style="display:none"></span>
      <div class="form-group">
         <label>Your password</label>
         <input class="form-control" type="password" id="password1" name="password"> <i class="icon_lock_alt"></i> 
      </div>
      <span class="qc" id="password1_c" style="display:none"></span>
      <div class="form-group">
         <label>Confirm password</label>
         <input class="form-control" type="password" id="password2" name="password2"> <i class="icon_lock_alt"></i> 
      </div>
      <div class="form-group">
         <div class="fileinput-preview thumbnail upimg hidebtnlg" data-trigger="fileinput" style="margin: auto;
            text-align: center;">
            <img id='img-upload' class="btn-file" src="{{URL::to('/')}}/public/avatar.png"></br>
            <label for="imgInp" class="btn btn-default btn-file">
               <p class="pointer mt-3 mb-0">Upload Profile Picture</p>
            </label>
            <input type="file" id="imgInp" name="image" required class="d-none"> 
         </div>
      </div>
      <span class="qc" id="password2_c" style="display:none"></span>
      <div id="pass-info" class="clearfix"></div>
      <!--  <a id="sign-out-button" type="button"  class="btn_1 rounded full-width add_top_30" style="color: white;"> <i class="fa fa-spinner fa-spin d-none"></i><span id="processing"> Register  <span></a>
         <div class="text-center add_top_10">Already have an acccount? <strong><a href="{{URL::to('/login')}}">Sign In</a> -->
      <button disabled class="btn_1 rounded full-width add_top_30"  id="sign-in-button">Sign-in</button>
      <!-- Button that handles sign-out -->
      <button class="mdl-button mdl-js-button mdl-button--raised" id="sign-out-button">Sign-out</button>
      <input name="device_id" value="website" type="hidden">
      <input name="device_type" value="WEBSITE" type="hidden">
      <input name="fcm_token" value="fcm_token" type="hidden">
      <input name="fcm_id" value="fcm_id" type="hidden">
   </form>
   <form id="verification-code-form" action="#">
      <!-- Input to enter the verification code -->
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
         <input class="mdl-textfield__input" type="text" id="verification-code">
         <label class="mdl-textfield__label" for="verification-code">Enter the verification code...</label>
      </div>
      <!-- Button that triggers code verification -->
      <input type="submit" class="btn_1 rounded full-width add_top_30" id="verify-code-button" value="Verify Code"/>
      <!-- Button to cancel code verification -->
      <button class="mdl-button mdl-js-button mdl-button--raised d-none" id="cancel-verify-code-button">Cancel</button>
   </form>
   <!--form -->
   <br />
   <br />
   <div class="row" style="display:none;">
      <div class="col-md-12">
         <div class="card card-default">
            <div class="card-header">
               <div class="row">
                  <div class="col-md-12">
                     <strong>Laravelcode - User sign-in status</strong>
                  </div>
               </div>
            </div>
            <div class="card-body">
               <div class="user-details-container">
                  Firebase sign-in status: <span id="sign-in-status">Unknown</span>
                  <div>Firebase auth <code>currentUser</code> object value:</div>
                  <pre><code id="account-details">null</code></pre>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>


<script src="https://www.gstatic.com/firebasejs/4.9.1/firebase.js"></script>
<script type="text/javascript">
    // Initialize Firebase
    var config = {
      apiKey: "AIzaSyAW9Hy1ZIp-mj1YvxMNmblRgj144mFvE6o",
      authDomain: "findagaff-976a5.firebaseapp.com",
      databaseURL: "https://findagaff-976a5.firebaseio.com",
      projectId: "findagaff-976a5",
      storageBucket: "findagaff-976a5.appspot.com",
      messagingSenderId: "741437096858",
      appId: "1:741437096858:web:7577883a78d20852a3f023",
      measurementId: "G-X5X4SK9S1F"
    };
    firebase.initializeApp(config);

    var database = firebase.database();
  /**
   * Set up UI event listeners and registering Firebase auth listeners.
   */
  window.onload = function() {
    // Listening for auth state changes.
    firebase.auth().onAuthStateChanged(function(user) {
      if (user) {
        // User is signed in.
        var uid = user.uid;
        var email = user.email;
        var photoURL = user.photoURL;
        var phoneNumber = user.phoneNumber;
        var isAnonymous = user.isAnonymous;
        var displayName = user.displayName;
        var providerData = user.providerData;
        var emailVerified = user.emailVerified;
        console.log("return");


        /*
            OTP Verification
        */
       /* var reg_id = $('#reg_id').val();
        var api_token = $('#api_token').val();
        var request = $.ajax({
            url: "{{URL::to('firebaseotpverify')}}",
            method: "POST",
            data: JSON.stringify({user_id : reg_id ,apitoken: api_token}),
            contentType: "application/json",
            processData: false,
            headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'x-api-key':api_token },
           
          });        

            request.done(function (response) {
           
            $(".qc").hide('');
            $("#sign-in-button").text('Register Now!');
            if(response.status =='1'){
               
               window.location = "{{URL::to('/')}}";
             
            
                              
            }else{

              swal(response.message);
                
             }
             

            });
            request.fail(function (jqXHR, textStatus) {
                $("#processing").text('Register Now!');
                swal("Oops,Something went to wrong,Please try after sometime");
            });
*/

      }
      updateSignInButtonUI();
      updateSignInFormUI();
      updateSignOutButtonUI();
      updateSignedInUserStatusUI();
      updateVerificationCodeFormUI();
    });

    // Event bindings.
    document.getElementById('sign-out-button').addEventListener('click', onSignOutClick);
    document.getElementById('phone-number').addEventListener('keyup', updateSignInButtonUI);
    document.getElementById('phone-number').addEventListener('change', updateSignInButtonUI);
    document.getElementById('verification-code').addEventListener('keyup', updateVerifyCodeButtonUI);
    document.getElementById('verification-code').addEventListener('change', updateVerifyCodeButtonUI);
    document.getElementById('verification-code-form').addEventListener('submit', onVerifyCodeSubmit);
    document.getElementById('cancel-verify-code-button').addEventListener('click', cancelVerification);

   // [START appVerifier]
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('sign-in-button', {
      'size': 'invisible',
      'callback': function(response) {
        // reCAPTCHA solved, allow signInWithPhoneNumber.
         signup()
        
      }
    });
   // [END appVerifier]

    recaptchaVerifier.render().then(function(widgetId) {
      window.recaptchaWidgetId = widgetId;
      updateSignInButtonUI();
    });
  };

  /**
   * Function called when clicking the Login/Logout button.
   */
  function onSignInSubmit() {
    if (isPhoneNumberValid()) {
      window.signingIn = true;
      updateSignInButtonUI();
      var phoneNumber = getPhoneNumberFromUserInput();
      var appVerifier = window.recaptchaVerifier;
      firebase.auth().signInWithPhoneNumber(phoneNumber, appVerifier)
          .then(function (confirmationResult) {
            // SMS sent. Prompt user to type the code from the message, then sign the
            // user in with confirmationResult.confirm(code).
            window.confirmationResult = confirmationResult;
            window.signingIn = false;
            updateSignInButtonUI();
            updateVerificationCodeFormUI();
            updateVerifyCodeButtonUI();
            updateSignInFormUI();
          }).catch(function (error) {
            // Error; SMS not sent
            console.error('Error during signInWithPhoneNumber', error);
            window.alert('Error during signInWithPhoneNumber:\n\n'
                + error.code + '\n\n' + error.message);
            window.signingIn = false;
            updateSignInFormUI();
            updateSignInButtonUI();
          });
    }
  }

  /**
   * Function called when clicking the "Verify Code" button.
   */
  function onVerifyCodeSubmit(e) {
    e.preventDefault();
    if (!!getCodeFromUserInput()) {
      window.verifyingCode = true;
      updateVerifyCodeButtonUI();
      var code = getCodeFromUserInput();
      confirmationResult.confirm(code).then(function (result) {
        // User signed in successfully.
        var user = result.user;
        window.verifyingCode = false;
        window.confirmationResult = null;
        updateVerificationCodeFormUI();
      }).catch(function (error) {
        // User couldn't sign in (bad verification code?)
        console.error('Error while checking the verification code', error);
        window.alert('Error while checking the verification code:\n\n'
            + error.code + '\n\n' + error.message);
        window.verifyingCode = false;
        updateSignInButtonUI();
        updateVerifyCodeButtonUI();
      });
    }
  }

  /**
   * Cancels the verification code input.
   */
  function cancelVerification(e) {
    e.preventDefault();
    window.confirmationResult = null;
    updateVerificationCodeFormUI();
    updateSignInFormUI();
  }

  /**
   * Signs out the user when the sign-out button is clicked.
   */
  function onSignOutClick() {
    firebase.auth().signOut();
  }

  /**
   * Reads the verification code from the user input.
   */
  function getCodeFromUserInput() {
    return document.getElementById('verification-code').value;
  }

  /**
   * Reads the phone number from the user input.
   */
  function getPhoneNumberFromUserInput() {
     var code = $( "#countrycode option:selected" ).text();
    var phone = $( "#phone-number" ).val();
var phonenumber = code+' '+phone;
console.log(phonenumber);
    return phonenumber;
  }

  /**
   * Returns true if the phone number is valid.
   */
  function isPhoneNumberValid() {
    var pattern = /^\+[0-9\s\-\(\)]+$/;
    var phoneNumber = getPhoneNumberFromUserInput();
    return phoneNumber.search(pattern) !== -1;
  }

  /**
   * Re-initializes the ReCaptacha widget.
   */
  function resetReCaptcha() {
    if (typeof grecaptcha !== 'undefined'
        && typeof window.recaptchaWidgetId !== 'undefined') {
      grecaptcha.reset(window.recaptchaWidgetId);
    }
  }

  /**
   * Updates the Sign-in button state depending on ReCAptcha and form values state.
   */
  function updateSignInButtonUI() {
    document.getElementById('sign-in-button').disabled =
        !isPhoneNumberValid()
        || !!window.signingIn;
  }

  /**
   * Updates the Verify-code button state depending on form values state.
   */
  function updateVerifyCodeButtonUI() {
    document.getElementById('verify-code-button').disabled =
        !!window.verifyingCode
        || !getCodeFromUserInput();
  }

  /**
   * Updates the state of the Sign-in form.
   */
  function updateSignInFormUI() {
    
    onSignOutClick();
    console.log(firebase.auth().currentUser);console.log(window.confirmationResult);
    if (firebase.auth().currentUser || window.confirmationResult) {
      document.getElementById('sign-in-form').style.display = 'none';
    } else {
      resetReCaptcha();
      document.getElementById('sign-in-form').style.display = 'block';
    }
  }

  /**
   * Updates the state of the Verify code form.
   */
  function updateVerificationCodeFormUI() {
    if (!firebase.auth().currentUser && window.confirmationResult) {
      document.getElementById('verification-code-form').style.display = 'block';
    } else {
      document.getElementById('verification-code-form').style.display = 'none';
    }
  }

  /**
   * Updates the state of the Sign out button.
   */
  function updateSignOutButtonUI() {
    if (firebase.auth().currentUser) {
      document.getElementById('sign-out-button').style.display = 'block';
    } else {
      document.getElementById('sign-out-button').style.display = 'none';
    }
  }

  /**
   * Updates the Signed in user status panel.
   */
  function updateSignedInUserStatusUI() {
    var user = firebase.auth().currentUser;
    if (user) {
      document.getElementById('sign-in-status').textContent = 'Signed in';
      document.getElementById('account-details').textContent = JSON.stringify(user, null, '  ');
       console.log("return1");

        /*
            OTP Verification
        */
        var reg_id = $('#reg_id').val();
        if(reg_id > 0) {
          var api_token = $('#api_token').val();
          var request = $.ajax({
              url: "{{URL::to('firebaseotpverify')}}",
              method: "POST",
              data: JSON.stringify({user_id : reg_id ,apitoken: api_token}),
              contentType: "application/json",
              processData: false,
              headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'x-api-key':api_token },
             
            });        

              request.done(function (response) {
             
              $(".qc").hide('');
              $("#sign-in-button").text('Register Now!');
              if(response.status =='1'){
                 
                 window.location = "{{URL::to('/')}}";
               
              
                                
              }else{

                swal(response.message);
                  
               }
               

              });
              request.fail(function (jqXHR, textStatus) {
                  $("#processing").text('Register Now!');
                  swal("Oops,Something went to wrong,Please try after sometime");
              });
            }


    } else {
      document.getElementById('sign-in-status').textContent = 'Signed out';
      document.getElementById('account-details').textContent = 'null';
    }
  }
</script> 
<script>

        function signup(){

            $(".qc").hide('');
            var email = $('input[name=email]').val();
            var name = $('input[name=name]').val();
            var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
           if($.trim($('#name').val()) == ''){
            $("#name_c").show('');
            $("#name_c").text('Please enter your name');
           
          }else if($.trim($('#email').val()) == ''){
            $("#email_c").show('');
            $("#email_c").text('Please enter email address');
          }else if(reg.test(email) == false){
            $("#email_c").show('');
            $("#email_c").text('Please enter Your email');
          }else if($.trim($('#password1').val())== ''){
            $("#password1_c").show('');
            $("#password1_c").text('Please enter Your password');
          }else if($.trim($('#password2').val())== ''){
            $("#password2_c").show('');
            $("#password2_c").text('Please enter Your Confirm password');
          }else if(($('#password1').val()) != ($('#password2').val())){
            $("#password2_c").show('');
            $("#password2_c").text('Password Mismatch');
          }else{
          
            $("#sign-in-button").text('processing...');
          var e = document.getElementById("countrycode");
          var country_code = e.value;
          var mobile = $('input[name=phone-number]').val();
          var password = $('#password1').val();
           var api_token = $('#api_token').val();
           var formdata = new FormData();
           var poData = jQuery(document.forms['sign-in-form']).serializeArray();
           for (var i=0; i<poData.length; i++)
        formdata.append(poData[i].name, poData[i].value);
        var imgfile = document.getElementById("imgInp");
        if($('input[id="imgInp"]').val()!=""){
                 formdata.append('profile_image', $('input[id="imgInp"]')[0].files[0]);
        }
        
        // $("#hiddenbtnid").addClass('disabled').attr("href", "#");
         $("#hiddenbtnid").addClass('hidden');
          var request = $.ajax({
            url: "{{URL::to('registerlogin')}}",
            method: "POST",
            data:formdata,
            contentType: false,
            processData: false,
            headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
           
          });        

            request.done(function (response) {
           
            $(".qc").hide('');
            $("#sign-in-button").text('Register Now!');
            if(response.status =='1'){
              $('#reg_id').val(response.data.id);
              $('#api_token').val(response.data.api_token);
              onSignInSubmit();
             //  window.location = "{{URL::to('otp')}}";
             
            
                              
            }else{
              
              $('.fa-spin').hide(); 
            //  $("#hiddenbtnid").addClass('disabled').removeAttr("href");
              $("#hiddenbtnid").removeClass('hidden');
              $("#password2_c").show('');
              $("#password2_c").text(response.message);
                
             }
             

            });
            request.fail(function (jqXHR, textStatus) {
                $("#processing").text('Register Now!');
                swal("Oops,Something went to wrong,Please try after sometime");
            });
          }
        } 



</script> 