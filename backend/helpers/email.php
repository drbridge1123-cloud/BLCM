<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email via SMTP using PHPMailer.
 *
 * @param string $to        Recipient email address
 * @param string $subject   Email subject line
 * @param string $htmlBody  Full HTML body
 * @param array  $options   Optional: 'cc', 'bcc', 'attachments', 'replyTo', 'smtp_email', 'smtp_password', 'from_name'
 * @return array ['success' => bool, 'message_id' => string|null, 'error' => string|null]
 */
function sendEmail($to, $subject, $htmlBody, $options = []) {
    // Per-user SMTP or global fallback
    $smtpUser = !empty($options['smtp_email']) ? $options['smtp_email'] : SMTP_USERNAME;
    $smtpPass = !empty($options['smtp_password']) ? $options['smtp_password'] : SMTP_PASSWORD;
    $fromEmail = !empty($options['smtp_email']) ? $options['smtp_email'] : SMTP_FROM_EMAIL;
    $fromName = !empty($options['from_name']) ? $options['from_name'] . ' - ' . FIRM_NAME : SMTP_FROM_NAME;

    if (empty($smtpUser) || empty($smtpPass)) {
        return [
            'success'    => false,
            'message_id' => null,
            'error'      => 'Email not configured. Please set SMTP credentials in settings or backend/config/email.php'
        ];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = SEND_TIMEOUT;

        $mail->setFrom($fromEmail, $fromName);

        if (!empty($options['replyTo'])) {
            $mail->addReplyTo($options['replyTo']);
        }

        $mail->addAddress($to);

        if (!empty($options['cc'])) {
            foreach ((array)$options['cc'] as $cc) {
                $mail->addCC($cc);
            }
        }
        if (!empty($options['bcc'])) {
            foreach ((array)$options['bcc'] as $bcc) {
                $mail->addBCC($bcc);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Convert base64 embedded images to CID inline attachments
        $cidCounter = 0;
        $htmlBody = preg_replace_callback(
            '/src=["\']data:image\/(png|jpe?g|gif);base64,([^"\']+)["\']/i',
            function($matches) use ($mail, &$cidCounter) {
                $type = $matches[1];
                $base64Data = $matches[2];
                $imageData = base64_decode($base64Data);
                if ($imageData === false) return $matches[0];

                $cidCounter++;
                $cid = 'img_embed_' . $cidCounter;
                $ext = ($type === 'jpeg' || $type === 'jpg') ? 'jpg' : $type;
                $mail->addStringEmbeddedImage($imageData, $cid, "image_{$cidCounter}.{$ext}", 'base64', "image/{$type}");
                return 'src="cid:' . $cid . '"';
            },
            $htmlBody
        );

        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(
            ['<br>', '<br/>', '<br />', '</p>'],
            "\n",
            $htmlBody
        ));
        $mail->CharSet = 'UTF-8';

        if (!empty($options['attachments'])) {
            foreach ($options['attachments'] as $attachment) {
                if (is_array($attachment)) {
                    // ['path' => ..., 'name' => ...]
                    if (file_exists($attachment['path'])) {
                        $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    }
                } elseif (is_string($attachment) && file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }

        $mail->send();

        return [
            'success'    => true,
            'message_id' => $mail->getLastMessageID(),
            'error'      => null
        ];
    } catch (Exception $e) {
        return [
            'success'    => false,
            'message_id' => null,
            'error'      => $mail->ErrorInfo
        ];
    }
}
