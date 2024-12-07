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
            formData.append('userLogins', 'LoginUser');
            
            $.ajax({
              method:'post',
              data: formData,
              contentType: false, 
              processData: false,
              dataType: 'JSON',
              success:function(response) {
                  
                  if(response.response_category == '200'){
                    let msg = "Login Successful";
                    swal('Great!', msg, 'success').then(function(){
                        window.location.href = "<?=lifetech_site_host_address()?>/Dashboard";
                    });
                    fx_lifetech_button_loader_close();  
                  }else if(response.response_category == '100'){
                      //let msg = "Invalid Login Details";
                        //for account deactivated use response_code = 115
                      //for incorrect password use response_code = 101
                      //for user not/record  found  use response_code = 103
                      
                      let msg = response.response_result;
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
            
              let email = $("#lifetech_email").val();
            
              if($("#lifetech_firstname").val() == ''){
                  let msg = "firstname field cannot be empty";
                  swal('Notice!', msg, 'warning');
                  fx_lifetech_button_loader_close();
                  return;
              }
              if($("#lifetech_lastname").val() == ''){
                  let msg = "lastname field cannot be empty";
                  swal('Notice!', msg, 'warning');
                  fx_lifetech_button_loader_close();
                  return;
              }
              if(email == '' && $("#lifetech_username").val() == ''){
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
              
             let password = $('#lifetech_password').val();
             let confirmPassword = $('#confirm').val();
             let errorMessage = $('#error-message');
              let lifetech_password;
    
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
            formData.append('userReg', 'RegUser');
            
            $.ajax({
              method:'post',
              data: formData,
              contentType: false, 
              processData: false,
              dataType: 'JSON',
              success:function(response) {
                  //console.log(response);
                   if(response.response_category == '200'){
                     let msg = "Registration Successful";
                     swal('Great!', msg, 'success').then(function(){
                       //  window.location.href = 
                       fx_lifetech_button_loader_close();
                     })  
                   }else if(response.response_category == '100'){
                       let msg = response.response_result;
                       swal('Sorry!', msg, 'warning');
                       fx_lifetech_button_loader_close();
                   }
                    
            },
                error: function(xhr, status, error) {
                    console.error('Error fetching files:', error);
                }
        });
            
        })
        //fx_lifetech_button_loader_close();
        
    })
    
</script>
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      