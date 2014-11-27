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
     * Mailer contracts.
     *
     * @package PHY\Mailer\IMailer
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IMailer
    {

        /**
         * Initiate the Mailer class with it's config.
         *
         * @param array $config
         */
        public function __construct(array $config = []);

        /**
         * Set our to: address(es)
         *
         * @param array $to
         * @return $this
         */
        public function setTo(array $to = []);

        /**
         * Set any bccs we may have.
         *
         * @param array $bcc
         * @return $this
         */
        public function setBcc(array $bcc = []);

        /**
         * Set our from: address.
         *
         * @param string $from
         * @return $this
         */
        public function setFrom($from);

        /**
         * Set our email's subject.
         *
         * @param string $subject
         * @return $this
         */
        public function setSubject($subject);

        /**
         * Set our email's body content.
         *
         * @param string $content
         * @param string $type
         * @return mixed
         */
        public function setBody($content, $type = 'html');

        /**
         * Add an additional header.
         *
         * @param string $header
         * @param string $value
         * @return $this
         */
        public function addHeader($header, $value);

        /**
         * Attach a file along with our email.
         *
         * @param string $file
         * @return $this
         */
        public function attach($file);

        /**
         * Send our email.
         *
         * @return bool
         */
        public function send();

    }
