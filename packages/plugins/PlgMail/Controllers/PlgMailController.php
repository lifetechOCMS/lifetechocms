<?php
namespace Lt\Plugins\PlgMail\Controllers;

// use Lt\Plugins\PlgMail\Services\PlgMailService;       
           
class PlgMailController
{
    
 public function test()
    {
        
        return "connect";
        
        $mail = new PlgMailService();
//adedayoadetunji7@gmail.com 
//lifetechocms2016@gmail.com
        $sent = $mail->send(
            'lifetechocms2016@gmail.com',
            'Test Email end from LifeTechOCMS for more time',
            '<h1>Hello from LifeTechOCMS 🚀</h1>'
        );
        //echo "email sent";
        echo $sent ? 'Email sent successfully' : 'Failed to send email';
    }
    
}

