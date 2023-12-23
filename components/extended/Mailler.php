<?php

/**
 * Copyright 2023-2024 Christophe LEMOINE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the “Software”),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */
declare(strict_types=1);

namespace components\extended {

    use components\core\Route;
    use components\extended\TwigWrapper;
    use Exception;
    use PHPMailer\PHPMailer\PHPMailer;
    use const SMTP_AUTH;
    use const SMTP_FROM_MAIL;
    use const SMTP_FROM_NAME;
    use const SMTP_HOST;
    use const SMTP_PASSWORD;
    use const SMTP_PORT;
    use const SMTP_REPLY_MAIL;
    use const SMTP_REPLY_NAME;
    use const SMTP_SECURE;
    use const SMTP_USERNAME;

    class Mailler {

        private ?TwigWrapper $twig = null;

        public static function __required(): void {
            if (!defined('SMTP_HOST'))
                throw new Exception("SMTP_HOST parameter not defined in config file.");
            if (!defined('SMTP_AUTH'))
                throw new Exception("SMTP_AUTH parameter not defined in config file.");
            if (!defined('SMTP_USERNAME'))
                throw new Exception("SMTP_USERNAME parameter not defined in config file.");
            if (!defined('SMTP_PASSWORD'))
                throw new Exception("SMTP_PASSWORD parameter not defined in config file.");
            if (!defined('SMTP_SECURE'))
                throw new Exception("SMTP_SECURE parameter not defined in config file.");
            if (!defined('SMTP_PORT'))
                throw new Exception("SMTP_PORT parameter not defined in config file.");
            if (!defined('SMTP_FROM_NAME'))
                throw new Exception("SMTP_FROM_NAME parameter not defined in config file.");
            if (!defined('SMTP_FROM_MAIL'))
                throw new Exception("SMTP_FROM_MAIL parameter not defined in config file.");
            if (!defined('SMTP_REPLY_NAME'))
                throw new Exception("SMTP_REPLY_NAME parameter not defined in config file.");
            if (!defined('SMTP_REPLY_MAIL'))
                throw new Exception("SMTP_REPLY_MAIL parameter not defined in config file.");

            Route::extendWith(Mailler::class);
        }

        public function __construct(TwigWrapper $twig) {
            $this->twig = $twig;
        }

        public function send(array|string $to, string $subject, string $message, bool $is_html = true, string $language = 'fr'): bool {
            $mail = new PHPMailer(true);

            try {
                //$mail->SMTPDebug  = DEBUG ? 2 : 0;
                $mail->setLanguage($language);
                $mail->CharSet = "UTF-8";
                $mail->Encoding = 'base64';
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = SMTP_AUTH;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = (SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : (SMTP_SECURE === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : ''));
                $mail->Port = SMTP_PORT;

                $mail->setFrom(SMTP_FROM_MAIL, SMTP_FROM_NAME);
                if (is_array($to)) {
                    $mail->addAddress($to['mail'], $to['name']);
                } else {
                    $mail->addAddress(strval($to));
                }
                $mail->addReplyTo(SMTP_REPLY_MAIL, SMTP_REPLY_NAME);

                $mail->isHTML($is_html);
                $mail->Subject = $subject;
                $mail->Body = $message;
                if (!$is_html)
                    $mail->AltBody = strip_tags(nl2br($message));

                return $mail->send();
            } catch (Exception $e) {
                return false;
            }
        }

        public function sendFromTwig(array|string $to, string $subject, string $templateName, array $data = [], string $language = 'fr'): bool {
            $content = $this->twig->toString($templateName, $data);
            return $this->send($to, $subject, $content, true, $language);
        }
    }

}