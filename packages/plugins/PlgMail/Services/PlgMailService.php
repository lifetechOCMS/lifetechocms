<?php
namespace Lt\Plugins\PlgMail\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
        
class PlgMailService
{
    public function send(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'abolorea@gmail.com';
            $mail->Password   = 'ycubzbkzmlqdbqit';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Debug
            //$mail->SMTPDebug  = SMTP::DEBUG_SERVER;
            //$mail->Debugoutput = 'html';

            $mail->setFrom('abolorea@gmail.com', 'LifeTechOCMS');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
          //  echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        }
    }
}

