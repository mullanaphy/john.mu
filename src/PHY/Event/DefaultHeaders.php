<?php

    /**
     * jo.mu
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to john@jo.mu so we can send you a copy immediately.
     */

    namespace PHY\Event;

    /**
     * Add our facebook link.
     *
     * @package PHY\Event\Menu
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class DefaultHeaders
    {

        /**
         * We're to add default headers to all requests.
         *
         * @param Item $event
         */
        public static function set(Item $event)
        {
            foreach ($event->getApp()->get('config/headers') as $key => $value) {
                $event->response->setHeader($key, $value);
            }
        }
    }
