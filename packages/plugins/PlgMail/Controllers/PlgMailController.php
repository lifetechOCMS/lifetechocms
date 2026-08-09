<?php
namespace Lt\Plugins\PlgMail\Controllers;

use Lt\Plugins\PlgMail\Services\PlgMailService; 

use Lt\Modules\MdLt\Services\LtResponse;
           
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
    
    
    public function send()
    {
        $mail = new PlgMailService();
        //adedayoadetunji7@gmail.com 
        //lifetechocms2016@gmail.com
        $sent = $mail->send(
            'lifetechocms2016@gmail.com',
            'Installation Feedback',
            '<h1>Thank your for installing the framework 🚀</h1>'
        );
        //echo "email sent";
        echo $sent ? 'Email sent successfully' : 'Failed to send email';
    }
    
    public function storeMail(){
    $url = "https://www.lifetech.host/hubs/api/v1/v2/queues";

    $headers = [
        "Accept: application/json",
    ];
    
    $data = [
        "email" => 'tunjiadedayo@gmail.com',
        "name" => "John Doe",
        "token" => "12345678"
    ];

    $responseHeaders = [];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
            $length = strlen($header);
            $parts = explode(':', $header, 2);

            if (count($parts) == 2) {
                $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }

            return $length;
        }
    ]);

    $body = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);
    
    // if($statusCode >= 200 && $statusCode < 300){
        
    //     return LtResponse::json('success', $statusCode, 200, $body);
    // }else{
    //      return LtResponse::json('failed', $statusCode, 100, $error);
    // }
    
    
    return [
        "success" => ($statusCode >= 200 && $statusCode < 300),
        "status" => $statusCode,
        "error" => $error ?: null,
        // "headers" => $responseHeaders,
        "body" => $body
    ];
}
    
    
    
}

