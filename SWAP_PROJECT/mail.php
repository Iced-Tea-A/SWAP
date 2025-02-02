<?php
/**
 */



// 2) Otherwise, if you manually downloaded PHPMailer, uncomment and adjust paths:
require_once 'PHPMailer\src\PHPMailer.php';
require_once 'PHPMailer\src\Exception.php';
require_once 'PHPMailer\src\SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Generates a random password string of the specified length.
 *
 * @param  int    $length  The length of the generated password (default 8).
 * @return string          The randomly generated password.
 */
function generateRandomPassword($length = 40 ): string
{
    // You can customize the character set as needed
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_-+=[]{}|:;<>?/*~';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Sends an email containing the given temporary password to the target email.
 *
 * @param  string  $targetEmail       The recipient email address.
 * @param  string  $generatedPassword The generated password to include in the email body.
 * @return bool                       True if email sent successfully, otherwise false.
 */
function sendResetPasswordEmail($targetEmail, $generatedPassword): bool
{
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();                             // Use SMTP
        $mail->Host       = 'smtp.gmail.com';         // e.g., smtp.gmail.com
        $mail->SMTPAuth   = true;                     // Enable SMTP auth
        $mail->Username   = 'muhammadaizrylreyestaybin@gmail.com';   // Your Gmail
        $mail->Password   = 'hbvx wtks uynx fquy';      // App Password (or hosting SMTP password)
        $mail->SMTPSecure = 'tls';                    // Encryption: 'tls' or 'ssl'
        $mail->Port       = 587;                      // 587 for 'tls', 465 for 'ssl'
        
        // Sender & recipient
        $mail->setFrom('muhammadaizrylreyestaybin@gmail.com', 'Administrator');
        $mail->addAddress($targetEmail);              // Add a recipient
        
        // Email content
        $mail->isHTML(false);                         // Set to true if you want HTML content
        $mail->Subject = 'Your Temporary Password';
        $mail->Body    = "Dear user,\n\n"
                       . "Here is your temporary password: {$generatedPassword}\n\n"
                       . "Please use this to log in and reset your password.\n\n"
                       . "Best regards,\nSchool Admin";

        // Attempt to send
        $mail->send();
        return true;
    } catch (Exception $e) {
        // For debugging: 
        // echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}
