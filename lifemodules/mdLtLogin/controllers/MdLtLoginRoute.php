<?php


///  Login Route
LtRoute::post('/user-accounts/login', 'mdLtLogin@TbRegistrationsController@login');
/// Sign Up route
LtRoute::post('/user-accounts/signup', 'mdLtLogin@TbRegistrationsController@signup');
/// change password route
LtRoute::post('/user-accounts/change-password', 'mdLtLogin@TbRegistrationsController@changePassword');
/// forgot password generate and send token route
LtRoute::patch('user-accounts/token/{email}', 'mdLtLogin@TbRegistrationsController@sendToken');
/// forgot password validate token route
LtRoute::patch('/user-accounts/token/validate/{email}/{token}', 'mdLtLogin@TbRegistrationsController@validateToken');
/// forgot password  route
LtRoute::post('/user-accounts/forgot-password', 'mdLtLogin@TbRegistrationsController@setPassword'); 


?>
      