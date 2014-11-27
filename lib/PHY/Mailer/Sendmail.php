<?php

    /**
     * Phyneapple!
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to license@phyneapple.com so we can send you a copy immediately.
     *
     */

    namespace PHY\Mailer;

    /**
     * Use Sendmail with our app.
     *
     * @package PHY\Mailer\Mailgun
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Sendmail implements IMailer
    {

        private $body = [
            'text' => null,
            'html' => null
        ];
        private $subject = '';
        private $attachments = [];
        private $to = 'root@localhost';
        private $cc = [];
        private $bcc = [];
        private $from = 'root@localhost';
        private $headers = [];

        /**
         * Initiate the Mailer class with it's config.
         *
         * @param array $config
         */
        public function __construct(array $config = [])
        {
        }

        /**
         * Set our to: address(es)
         *
         * @param array $to
         * @return $this
         */
        public function setTo(array $to = [])
        {
            $this->to = array_pop($to);
            $this->cc = [];
            foreach ($to as $someone) {
                $this->cc[] = $someone;
            }
            return $this;
        }

        /**
         * Set any bccs we may have.
         *
         * @param array $bcc
         * @return $this
         */
        public function setBcc(array $bcc = [])
        {
            $this->bcc = $bcc;
            return $this;
        }

        /**
         * Set our from: address.
         *
         * @param string $from
         * @return $this
         */
        public function setFrom($from)
        {
            $this->from = $from;
            return $this;
        }

        /**
         * Set our email's subject.
         *
         * @param string $subject
         * @return $this
         */
        public function setSubject($subject)
        {
            $this->subject = $subject;
            return $this;
        }

        /**
         * Set our email's body content.
         *
         * @param string $content
         * @param string $type
         * @return mixed
         */
        public function setBody($content, $type = 'html')
        {
            $this->body[$type] = $content;
            return $this;
        }

        /**
         * Add an additional header.
         *
         * @param string $header
         * @param string $value
         * @return $this
         */
        public function addHeader($header, $value)
        {
            $this->headers[$header] = $value;
            return $this;
        }

        /**
         * Attach a file along with our email.
         *
         * @param string $file
         * @throws Exception
         * @return $this
         */
        public function attach($file)
        {
            if (!is_readable($file)) {
                throw new Exception('Cannot read file "' . $file . '".');
            }
            $this->attachments[] = $file;
            return $this;
        }

        /**
         * Send our email.
         *
         * @return bool
         */
        public function send()
        {

            $headers = $this->headers;
            $headers['From'] = $this->from;
            $headers['Reply-To'] = $this->from;
            if ($this->cc) {
                $headers['Cc'] = implode(', ', $this->cc);
            }
            if ($this->bcc) {
                $headers['Bcc'] = implode(', ', $this->bcc);
            }
            if ($this->body['html'] && $this->body['text']) {
                if ($this->attachments) {
                    $boundary = '--PHP-mixed-' . md5(rand(0, 1000000000) . time());
                    $innerBoundary = '--PHP-mixed-' . md5(rand(0, 1000000000) . time());
                    $headers['Content-Type'] = 'multipart/alternative; boundary="' . $boundary . '"';
                    $body = $boundary . "\r\n";
                    $body .= 'Content-Type: multipart/alternative; boundary="' . $innerBoundary . '"' . "\r\n\r\n";
                    $body .= $innerBoundary . "\r\n";
                    $body .= 'Content-Type: text/plain charset="utf-8"' . "\r\n";
                    $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
                    $body .= $this->body['text'] . "\r\n\r\n";
                    $body .= $innerBoundary . "\r\n";
                    $body .= 'Content-Type: text/html charset="utf-8"' . "\r\n";
                    $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
                    $body .= $this->body['html'] . "\r\n\r\n";
                    $body .= $innerBoundary . "\r\n\r\n";
                    foreach ($this->attachments as $attachment) {
                        $body .= self::getAttachment($attachment, $boundary);
                    }
                } else {
                    $boundary = '--PHP-alt-' . md5(rand(0, 1000000000) . time());
                    $headers['Content-Type'] = 'multipart/alternative; boundary="' . $boundary . '"';
                    $body = $boundary . "\r\n";
                    $body .= 'Content-Type: text/plain charset="utf-8"' . "\r\n";
                    $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
                    $body .= $this->body['text'] . "\r\n\r\n";
                    $body .= $boundary . "\r\n";
                    $body .= 'Content-Type: text/html charset="utf-8"' . "\r\n";
                    $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
                    $body .= $this->body['html'] . "\r\n\r\n";
                    $body .= $boundary;
                }
            } else {
                if ($this->body['html']) {
                    $type = 'html';
                } else {
                    $type = 'text';
                }
                if ($this->attachments) {
                    $boundary = '--PHP-mixed-' . md5(rand(0, 1000000000) . time());
                    $headers['Content-Type'] = 'multipart/alternative; boundary="' . $boundary . '"';
                    $body = $boundary . "\r\n";
                    $body .= 'Content-Type: text/' . $type . ' charset="utf-8"' . "\r\n";
                    $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
                    $body .= $this->body[$type] . "\r\n\r\n";
                    foreach ($this->attachments as $attachment) {
                        $body .= self::getAttachment($attachment, $boundary);
                    }
                } else {
                    $headers['Content-Type'] = 'text/' . $type . '; charset="utf-8"';
                    $body = $this->body[$type];
                }
            }

            $parsedHeaders = [];
            foreach ($headers as $header => $value) {
                $parsedHeaders .= $header . ': ' . $value;
            }
            $parsedHeaders = implode("\r\n", $parsedHeaders);

            return mail($this->to, $this->subject, $body, $parsedHeaders);
        }

        /**
         * Generate the appropriate string for attachment bodies.
         *
         * @param string $attachment
         * @param string $boundary
         * @return string
         * @ignore
         */
        private static function getAttachment($attachment, $boundary)
        {
            $file = explode(DIRECTORY_SEPARATOR, $attachment);
            $file = $file[count($file) - 1];

            if (function_exists('finfo')) {
                $contentType = finfo(FILEINFO_MIME, $attachment);
            } else if (class_exists('\MIME\Type')) {
                $contentType = \MIME\Type::autoDetect($file);
            } else {
                $contentType = mime_content_type($attachment);
            }

            $body = $boundary . "\r\n";
            $body .= 'Content-Type: ' . $contentType . '; name="' . $file . '"' . "\r\n";
            $body .= 'Content-Transfer-Encoding: base64' . "\r\n";
            $body .= 'Content-Disposition: attachment' . "\r\n\r\n";
            $body .= chunk_split(base64_encode(file_get_contents($attachment)));
            $body .= $boundary . "\r\n\r\n";
            return $body;
        }
    }
