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

    use Mailgun\Mailgun as Client;

    /**
     * Use Mailgun with our app.
     *
     * @package PHY\Mailer\Mailgun
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Mailgun implements IMailer
    {

        private $config = [];
        private $mailer;
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
            $this->config = $config;
            $this->mailer = new Client($this->config['key']);
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
         * @return $this
         */
        public function attach($file)
        {
            return $this;

        }

        /**
         * Send our email.
         *
         * @return bool
         */
        public function send()
        {
            $builder = $this->mailer->messageBuilder();
            $builder->addToRecipient($this->to);
            $builder->setFromAddress($this->from);
            $builder->setReplyToAddress($this->from);
            foreach ($this->cc as $address) {
                $builder->addCcRecipient($address);
            }
            foreach ($this->bcc as $address) {
                $builder->addBccRecipient($address);
            }
            $builder->setSubject($this->subject);
            if ($this->body['html']) {
                $builder->setHtmlBody($this->body['html']);
            }
            if ($this->body['text']) {
                $builder->setTextBody($this->body['html']);
            }
            foreach ($this->attachments as $file) {
                $builder->addAttachment($file);
            }
            foreach ($this->headers as $header => $value) {
                $builder->addCustomHeader($header, $value);
            }
            return $this->mailer->post($this->config['domain'] . '/messages', $builder->getMessage(), $builder->getFiles());
        }
    }
