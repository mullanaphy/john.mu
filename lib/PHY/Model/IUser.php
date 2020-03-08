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

    namespace PHY\Model;

    use PHY\Model\IEntity;
    use PHY\Encoder\IEncoder;

    /**
     * User contract.
     *
     * @package PHY\Model\IUser
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IUser extends IEntity
    {

        /**
         * Check to see if a password matches what it should.
         *
         * @param string $password
         * @param string $checkPassword
         * @return boolean
         * @throws Exception
         */
        public function checkPassword($password = '', $checkPassword = null);

        /**
         * Set our password encoder.
         *
         * @param IEncoder $encoder
         * @return $this
         */
        public function setEncoder(IEncoder $encoder);

        /**
         * Grab our password encoder.
         *
         * @return IEncoder
         */
        public function getEncoder();
    }
