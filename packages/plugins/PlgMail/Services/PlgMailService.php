<?php
namespace Lt\Plugins\PlgMail\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

//(string $to, string $subject, string $body

class PlgMailService
{
    public function send(array $sender, array $template, array $receiver)
    {
        $mail = new PHPMailer(true);

        try {
            
            if (empty($sender)) {
                // sender not providedycubzbkzmlqdbqitllll
                  
                $mail->Host       = 'smtp.gmail.com';
                $mail->Username   = 'lifetechocms2016@gmail.com';
                $mail->Password   = 'nveppwaocccvtrtl';
                $mail->Port       = 587;
                $mail->setFrom('lifetechocms2016@gmail.com', 'OGITECH SU');
                 $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
               
                /*
                $mail->Host       = 'mail.lifetech.host';
                $mail->Username   = 'noreply@lifetech.host';
                $mail->Password   = 'aUBtpY;wj]d4';
                $mail->Port       = 465;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->setFrom('noreply@lifetech.host', 'OGITECH SU');
                */
            }else{
                $fromEmail =$sender['fromEmail'];  $fromName = $sender['fromName'];
                
                $mail->Host       = $sender['Host'];
                $mail->Username   = $sender['Username'];
                $mail->Password   = $sender['Password'];
                $mail->Port       = $sender['Port'];
                $mail->setFrom($fromEmail, $fromName);
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            $mail->isSMTP();
            $mail->SMTPAuth   = true;
            $mail->isHTML(true);
           
            
            $mail->addAddress($receiver['toEmail']);
            $mail->Subject = $template['subject'];
            $mail->Body    = $template['bodyText'];

            // Debug
            //$mail->SMTPDebug  = SMTP::DEBUG_SERVER;
            //$mail->Debugoutput = 'html';
            $mail->send();
            $toReturn = ["responseCategory"=>"200"];
            return $toReturn;
        } catch (Exception $e) {
            //  echo 'Mailer Error: ' . $mail->ErrorInfo;
            $toReturn = ["responseCategory"=>"100",$responseData=$mail->ErrorInfo];
            return $toReturn;
        }
    }
}

