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

    namespace PHY\Cache;

    /**
     * Using no cache. This gets set if no caching options are available and
     * disk caching is disabled.
     *
     * @package PHY\Cache\None
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class None implements ICache
    {

        /**
         * Does nothing.
         *
         * @param array $settings
         */
        public function __construct(array $settings = [])
        {

        }

        /**
         * {@inheritDoc}
         */
        public function decrement($node, $decrement = 1)
        {
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function delete($node = false)
        {
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function flush()
        {
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function get($node, $flag = 0)
        {
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function increment($node, $increment = 1)
        {
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function replace($node, $value, $expiration = 0, $flag = 0)
        {
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function set($node, $value, $expiration = 0, $flag = 0)
        {
            return false;
        }

    }
