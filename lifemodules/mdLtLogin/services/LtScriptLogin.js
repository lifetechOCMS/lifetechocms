     <script>
      
      
      
$(function(){
  
    
    $('#logins').on('click','#logBtn', function(){
        fx_lifetech_button_loader_open();
        let username = $('#userName').val();
        let password = $('#userPassW').val();
        let errorMsg = '';
        
        // Check if username is empty
        if (username === '') {
            errorMsg = 'Username is required.';
            swal('Notice!', errorMsg, 'warning');
            fx_lifetech_button_loader_close();
            return;
        }

        // Check if password is empty
        if (password === '') {
            errorMsg = 'Password is required.';
            swal('Notice!', errorMsg, 'warning');
            fx_lifetech_button_loader_close();
            return;
        }

        let formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        // formData.append('userLogins', 'LoginUser');
        
        $.ajax({
          headers: {
            'Authorization': 'Bearer <?= LtSession::get('lw_token'); ?>'
          },
          url:'<?= ltSiteHostAddress() ?>/api/user-accounts/login',
          method:'post',
          data: formData,
          contentType: false, 
          processData: false,
          //dataType: 'text',
         dataType: 'JSON',
          success:function(response) {
   
              if(response.responseCategory == '200'){
                let msg = "Login Successful";
                swal('Great!', msg, 'success').then(function(){
                    window.location.href = "<?=lifetech_site_host_address()?>/Dashboard";
                });
                fx_lifetech_button_loader_close();  
              }else if(response.responseCategory == '100'){
                  //let msg = "Invalid Login Details";
                    //for account deactivated use response_code = 115
                  //for incorrect password use response_code = 101
                  //for user not/record  found  use response_code = 103
                  
                  let msg = response.responseResult;
                  //let msg = "weldone";
                  swal('Sorry!', msg, 'warning');
                  fx_lifetech_button_loader_close();
              }
              
                
        },
            error: function(xhr, status, error) {
                console.error('Error fetching files:', error);
                fx_lifetech_button_loader_close();
            }
    });
        
        
    })
    
})
    ///// End of login script
    
    
$(function(){
    
    $("#registration").on('click', '#RegBtn', function(){
        fx_lifetech_button_loader_open();
        // validate email function
        function validateEmail(email) {
          return email.match(
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
          );
        }
        
        // validate password function
    	function validatePassword(password) {
    	    
          var passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
          return passwordRegex.test(password);
        }
        
          let email = $("#lifetechEmail").val();
        
          if($("#lifetechFirstname").val() == ''){
              let msg = "firstname field cannot be empty";
              swal('Notice!', msg, 'warning');
              fx_lifetech_button_loader_close();
              return;
          }
          if($("#lifetechSurname").val() == ''){
              let msg = "lastname field cannot be empty";
              swal('Notice!', msg, 'warning');
              fx_lifetech_button_loader_close();
              return;
          }
          if(email == '' && $("#lifetechUsername").val() == ''){
              let msg = "Both Email and Username field cannot be empty, One must be filled";
              swal('Notice!', msg, 'warning');
              fx_lifetech_button_loader_close();
              return;
          }else if(!email == ''){
              if(!validateEmail(email)){
                  let msg = "Email address not valid";
                  swal('Notice!', msg, 'warning');
                  fx_lifetech_button_loader_close();
                  return;
              }
          }
          
         let password = $('#lifetechPassword').val();
         let confirmPassword = $('#confirm').val();
         let errorMessage = $('#error-message');
          let lifetechPassword;

         if (password === "" || confirmPassword === "") {
             errorMessage.text("Please fill out both Password fields.").fadeIn().delay(5000).fadeOut();
             fx_lifetech_button_loader_close();
             return;
         } else if (password !== confirmPassword) {
             errorMessage.text("Passwords do not match.").fadeIn().delay(5000).fadeOut();
             fx_lifetech_button_loader_close();
             return;
         } else {
             errorMessage.text(""); // Clear any previous error message

         }
       
   
        let formData = new FormData(document.getElementById('registration'));
        // formData.append('userReg', 'RegUser');
        
        $.ajax({
          headers: {
            'Authorization': 'Bearer <?= LtSession::get('lw_token'); ?>'
          },
          url:'<?= ltSiteHostAddress() ?>/user-accounts/signup',
          method:'post',
          data: formData,
          contentType: false, 
          processData: false,
          dataType: 'JSON',
          success:function(response) {
              //console.log(response);
               if(response.responseCategory == '200'){
                 let msg = "Registration Successful";
                 swal('Great!', msg, 'success').then(function(){
                   window.location.href = "<?=lifetech_site_host_address()?>/sign_up";
                   fx_lifetech_button_loader_close();
                  document.getElementById('registration').reset();
                 })  
               }else if(response.responseCategory == '100'){
                   let msg = response.responseResult;
                   swal('Sorry!', msg, 'warning');
                   fx_lifetech_button_loader_close();
               }
                
        },
            error: function(xhr, status, error) {
                console.error('Error fetching files:', error);
                fx_lifetech_button_loader_close();
            }
    });
        
    })
    //fx_lifetech_button_loader_close();
    
})

//// Registration Script End

/// Change Password Start

$(function(){
    
    $("#changePasswordBtn").click(function(){
        
        $("#changePasswordBtn").html('<span class="spinner-border spinner-border-sm"></span> Processing...');
         
        let newPassword = $('#newPassword').val();
        let confirmPassword = $('#confirmPassword').val();
        
        if($('#currentPassword').val() == ""){
            let msg = "Current password field must not be empty";
            swal('Oops!', msg, 'warning').then(function(){
                    $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                })
            // toastr.warning('Oops!', msg);
            
            return;
        }
        if(newPassword == ""){
            let msg = "New password field must not be empty";
            swal('Oops!', msg, 'warning').then(function(){
                    $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                })
            // toastr.warning('Oops!', msg);
            
            return;
        }
        
        if(newPassword !== confirmPassword){
            let msg = "The passwords you entered don't match. Please make sure they match.";
            swal('Oops!', msg, 'warning').then(function(){
                    $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                })
            // toastr.warning('Oops!', msg);
            
            return;
        }
         if(newPassword.length < 6){
            let msg = "Password requirement: Minimum 6 characters.";
            swal('Oops!', msg, 'warning').then(function(){
                    $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                })
             
            return;
        }
        
        //  fx_lifetech_button_loader_open();
        
        var myFormChangePassword = document.getElementById("frm_change_password");
        var formChangePassword = new FormData(myFormChangePassword);
        // formChangePassword.append('changePasswordAction', 'changePasswordValue');
        swal({
            title: 'Notice!',
            text: "Are you sure you want to change your password?",
            icon: 'warning',
            buttons: {
              cancel: true,
              confirm: true
                },
        }).then((confirmed) => {
            if (confirmed) {
                  $.ajax({
                    headers: {
                        'Authorization': 'Bearer <?= LtSession::get('lw_token'); ?>'
                      },
                    url:'<?= ltSiteHostAddress() ?>/api/user-accounts/change-password',
                    method: "POST",
                    data: formChangePassword,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function(response) {
                     
                      if (response.responseCategory == '200') {
                        let msg = response.responseResult;
                        swal('Great!', msg, 'success').then(function(){
                            $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                            document.getElementById("frm_change_password").reset();
                        })
                        
                        // fx_lifetech_button_loader_close();
                        
                      } else if (response.responseCategory == '100') {
                        let msg = response.responseResult;
                        swal('Notice!', msg, 'error').then(function(){
                           $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                            
                        })
                       
                      }
                    //   fx_lifetech_button_loader_close();
                    },
                    error: function() {
                      swal({
                        icon: 'error',
                        title: 'Oops',
                        text: 'An error occurred! Nothing has changed...'
                      }).then(function(){
                            $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                      })
                    }
                  });
            } else {
                    //   fx_lifetech_button_loader_close();
                    $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
            }
        });

    })

/////// Show Password 

    $(document).on('change', '#showPassword', function(){

    
         const passwordFields = ["currentPassword", "newPassword", "confirmPassword"];
            passwordFields.forEach(id => {
                const field = $(`#${id}`);
                let type = this.checked ? "text" : "password";
                field.attr('type', type);
            
        })
        
    })
    
})

$(function(){
    //// forgot password event 
    $('#sendToken').click(function(e) {
        e.preventDefault();

        // Validate email function
        const validateEmail = (email) => {
            return String(email)
                .toLowerCase()
                .match(
                    /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                );
        };

        // Get email and validate
        let email = $("#lifetechEmail").val();
        if (!validateEmail(email)) {
            swal('Notice!', 'Email address is not valid', 'warning');
            return;
        }

        // Show confirmation popup
        fx_lifetech_button_loader_open();
        swal({
            title: 'Notice!',
            text: "Click OK to proceed",
            icon: 'warning',
            buttons: {
                cancel: true,
                confirm: true,
            },
        }).then((confirmed) => {
            if (confirmed) {
                $.ajax({
                    headers: {
                        'Authorization': 'Bearer <?= LtSession::get('lw_token'); ?>'
                      },
                    url:`<?= ltSiteHostAddress() ?>/api/user-accounts/token/${email}`,                    
                    method: "PATCH",
                    dataType: "json",
                    //data:{'_method':'PATCH'},
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.responseCategory === '200') {
                            swal('Great!', "An authentication token has been sent to your email. Kindly proceed to your email to complete your validation process.", 'success').then(function() {
                                window.location.href = `<?=lifetech_site_host_address()?>/verify_token?sn=${email}`;
                                document.getElementById('forgot_password').reset();
                            });
                        } else {
                            swal('Notice!', response.responseResult, 'warning').then(function() {
                                fx_lifetech_button_loader_close();
                            });
                        }
                    },
                    error: function() {
                        swal({
                            icon: 'error',
                            title: 'Oops',
                            text: 'An error occurred! Nothing has changed...'
                        });
                        fx_lifetech_button_loader_close();
                    }
                });
            } else {
                fx_lifetech_button_loader_close();
            }
        });
    });
    
})

//////// validate otp, resend otp

$(function() { 
       // ------------------ VALIDATION STARTS -----------------------//
       
    const inputs = $("#otp > input");

    inputs.each(function(index) {
        $(this).on("input", function() {
            if (this.value.length === this.maxLength) {
                if (index < inputs.length - 1) {
                    inputs.eq(index + 1).focus();
                }else if(index === inputs.length - 1){
                  $('#valToken').trigger('click');
                }
            }
        });

        // Check for backspace to move focus back
        $(this).on("keydown", function(e) {
            if (e.key === "Backspace" && this.value.length === 0 && index > 0) {
                inputs.eq(index - 1).focus().val(""); // Clear previous input and focus
            }
        });
        
    });

    // Handle pasting the OTP across all inputs
    inputs.first().on("paste", function(e) {
        e.preventDefault();
        const pasteData = e.originalEvent.clipboardData.getData("text").trim();

        if (pasteData.length === inputs.length) {
            pasteData.split("").forEach((char, i) => {
                inputs.eq(i).val(char);
                
                 $('#valToken').trigger('click');
            });
            inputs.last().focus(); // Move focus to last input
        }else{
            swal('Oops', 'The Clipboard data is more than the input box', "warning");
        }
    });

$("#valToken").click(function(e) {
    e.preventDefault();

     let tokenValue = [];

        $('#otp input').each(function() {
            tokenValue.push($(this).val());  // Add each input field's value to the array
        });
        tokenValue = tokenValue.join('');
        if ( tokenValue== "") {
            var msg = "Token field cannot be empty";
            swal('Notice!', msg, 'warning');
            return;
        }

        const tokenRegex = /^[1-9]{6}$/;

        if (!tokenRegex.test(tokenValue)) {
            var msg = "Token is invalid";
            swal('Notice!', msg, 'warning');
            return;
        }

        fx_lifetech_button_loader_open();

        swal({
            title: 'Notice!',
            text: "Are you sure to validate this token?",
            icon: 'warning',
             buttons: {
                cancel: true,
                confirm: true
              },
        }).then((result) => {
            if (result) {
                    $.ajax({
                        headers: {
                            'Authorization': 'Bearer <?= LtSession::get('lw_token'); ?>'
                          },
                        url:`<?= ltSiteHostAddress() ?>/api/user-accounts/token/validate/${tokenValidateEmail}/${tokenValue}`,                         
                        method: "PATCH",
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        success: function(response) {

                            var responseCategory = response.responseCategory;
                            var responseCode = response.responseCode;
                            var responseData = response.responseData;
                            if (responseCategory == "200") {
                                let tokenValue = responseData.verifiedToken;
                                let userId = responseData.userId;
                                var msg = "You're set! Token Verified Successfully";
                                  swal('Great!', msg, 'success').then(function(){
                                        $('#otp input').each(function() {
                                           $(this).val('');  // empty the input field
                                        });
                                      window.location.href = `<?=lifetech_site_host_address()?>/reset_password?sn=${tokenValue}&id=${userId}`;

                                });
                            } else if(responseCode == "107"){
                                var msg = "Token Expired. Please Generate another Token";
                                    swal('Oops!', msg, 'warning').then(function(){
                                     $('#otp input').each(function() {
                                           $(this).val('');  // empty the input field
                                     });
                                });
                            }else {
                                var msg = response.responseResult;
                                swal('Oops!', msg, 'warning');
                            }
                            fx_lifetech_button_loader_close();
                        },
                    })
                    .fail(function() {
                        swal({
                            icon: 'error',
                            title: 'Oops',
                            text: 'An error occurred! Nothing has changed...'
                        });
                        fx_lifetech_button_loader_close();
                    });
            } else {
                fx_lifetech_button_loader_close();
            }
        })


    })
    // ------------------ VALIDATION ENDS -----------------------//
    
    // let counter = 2;
    function startTimer(duration, display) {
        let timer = duration, minutes, seconds;
        const interval = setInterval(function() {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.text(`Resend OTP in ${minutes}m : ${seconds}s`);

            if (--timer < 0) {
                clearInterval(interval);
                $("#resend").removeClass('d-none').fadeIn(5000);
                 display.text('');
            }
        }, 1000);
    }

    $("#resend").click(function() {
        // Logic to resend OTP
        // $('#sendResendEmail').trigger('click');
        resendEmail();
        
    });
   
 
   
      ////////   Resend Email
   
    function resendEmail(){

        // Show confirmation popup
        swal({
            title: 'Notice!',
            text: `Click ok to resend 6 digit token to ${tokenValidateEmail}`,
            icon: 'warning',
            buttons: {
                cancel: true,
                confirm: true,
            },
        }).then((confirmed) => {
            if (confirmed) {
                // Disable the button and restart the timer
                $("#resend").addClass('d-none').fadeOut(5000);
                startTimer(120, $("#timer"));
                // counter += 2;
                
                fx_lifetech_button_loader_open();
                $.ajax({
                    headers: {
                        'Authorization': 'Bearer <?= LtSession::get('lw_token'); ?>'
                      },
                    url:`<?= ltSiteHostAddress() ?>/api/user-accounts/token/${tokenValidateEmail}`,                     
                    method: "PATCH",
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.responseCategory === '200') {
                            swal('Great!', `An authentication token has been sent to ${tokenValidateEmail}. Kindly proceed to your email to complete your validation process.`, 'success').then(function() {
                               fx_lifetech_button_loader_close();
                            });
                        } else {
                            swal('Notice!', response.responseResult, 'warning').then(function() {
                                fx_lifetech_button_loader_close();
                            });
                        }
                    },
                    error: function() {
                        swal({
                            icon: 'error',
                            title: 'Oops',
                            text: 'An error occurred! Nothing has changed...'
                        });
                        fx_lifetech_button_loader_close();
                    }
                });
            } else {
                fx_lifetech_button_loader_close();
            }
        });
    }
 
    // Start the initial timer
    startTimer(120, $("#timer"));
   
  });  

$(function(){
    $("#setPassword").click(function(e) {
        e.preventDefault();
        let lifetechPassword = $('#newPassword').val();
        let lifetechConfirm = $('#confirmPassword').val();
        
          if (lifetechPassword == "") {
              var msg = "Password field cannot be empty";
              swal('Notice!', msg, 'warning').then(function(){
              });
                return;
            }
          if (lifetechPassword !== lifetechConfirm) {
              var msg = "Password does not match. Password and Confirm Password must be of the same value";
                swal('Notice!', msg, 'warning').then(function(){
              });
                return;
            }
         if(lifetechPassword.length < 6){
            let msg = "Password requirement: Minimum 6 characters.";
            swal('Oops!', msg, 'warning').then(function(){
                    $("#changePasswordBtn").html('<i class="bi bi-send"></i>  Submit');
                })
            // toastr.warning('Oops!', msg);
            
            return;
        }


        fx_lifetech_button_loader_open();

        swal({
            title: 'Notice!',
            text: "Are you sure to submit this record?",
            icon: 'warning',
             buttons: {
                cancel: true,
                confirm: true
              },
        }).then((result) => {
            if (result) {
                    $.ajax({
                        headers: {
                            'Authorization': 'Bearer <?= LtSession::get('lw_token'); ?>'
                          },
                        url:`<?= ltSiteHostAddress() ?>/api/user-accounts/forgot-password`,
                        method: "post",
                        data: {
                            'userId': userId,
                            'tokenValue': tokenValue,
                            'password':lifetechPassword,
                            'confirm':lifetechConfirm,
                        },
                        dataType: "json",
                        success: function(response) {
                            // console.log(response);
                            var responseCategory = response["responseCategory"];
                            var responseCode = response["response_code"];
                            if (responseCategory == "200") {
                                var msg = response.responseResult;
                                  swal('Great!', msg, 'success').then(function(){
                                      window.location.href = `<?=lifetech_site_host_address()?>/login`;

                                });
                            } else if(responseCategory == "100"){
                                var msg = response.responseResult;
                                swal('Oops!', msg, 'warning');
                            }
                            fx_lifetech_button_loader_close();
                        },
                    })
                    .fail(function() {
                        swal({
                            icon: 'error',
                            title: 'Oops',
                            text: 'An error occurred! Nothing has changed...'
                        });

                    });
            } else {
                fx_lifetech_button_loader_close();
            }
        })


    })
})
    
    
</script> 
      