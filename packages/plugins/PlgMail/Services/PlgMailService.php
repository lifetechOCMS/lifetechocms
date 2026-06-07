<?php
namespace Lt\Plugins\PlgMail\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

//(string $to, string $subject, string $body

class PlgMailService
{
    public function send(array $sender, array $template, array $receiver): bool
    {
        $mail = new PHPMailer(true);

        try {
            
            if (empty($sender)) {
                // sender not provided
                    
                $mail->Host       = 'smtp.gmail.com';
                $mail->Username   = 'abolorea@gmail.com';
                $mail->Password   = 'ycubzbkzmlqdbqit';
                $mail->Port       = 587;
                $mail->setFrom('abolorea@gmail.com', 'LifeTechOCMS');
            }else{
                $fromEmail =$sender['fromEmail'];  $fromName = $sender['fromName'];
                
                $mail->Host       = $sender['Host'];
                $mail->Username   = $sender['Username'];
                $mail->Password   = $sender['Password'];
                $mail->Port       = $sender['Port'];
                $mail->setFrom($fromEmail, $fromName);
            }

            $mail->isSMTP();
            $mail->SMTPAuth   = true;
            $mail->isHTML(true);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            
            $mail->addAddress($receiver['toEmail']);

            $mail->Subject = $template['subject'];
            $mail->Body    = $template['bodyText'];

            // Debug
            //$mail->SMTPDebug  = SMTP::DEBUG_SERVER;
            //$mail->Debugoutput = 'html';
            
            return $mail->send();
        } catch (Exception $e) {
          //  echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        }
    }
}

