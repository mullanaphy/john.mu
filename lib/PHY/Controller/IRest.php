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

    namespace PHY\Controller;

    /**
     * Interface for RESTful controllers.
     *
     * @package PHY\Controller\IRest
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IRest extends IController
    {

        /**
         * Create a successful message.
         *
         * @param mixed $message
         * @param int $status
         * @return \PHY\Response\Rest
         */
        public function success($message, $status = 200);

        /**
         * Create a failed message.
         *
         * @param mixed $message
         * @param int $status
         * @return \PHY\Response\Rest
         */
        public function error($message, $status = 500);
    }
