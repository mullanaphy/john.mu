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

    namespace PHY\Encoder;

    /**
     * Interface for encoders. Based off of PHPass to keep it simple.
     *
     * @package PHY\Encoder\IEncoder
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IEncoder
    {

        /**
         * Set our iteration and portable hashes on initialization.
         *
         * @param int $iteration_count_log2
         * @param boolean $portable_hashes
         */
        public function __construct($iteration_count_log2 = 8, $portable_hashes = false);

        /**
         * Hash a password.
         *
         * @param string $password
         * @return string
         */
        public function hashPassword($password);

        /**
         * Check a password against a hashed password.
         *
         * @param string $password
         * @param string $stored_hash
         * @return boolean
         */
        public function checkPassword($password, $stored_hash);
    }
