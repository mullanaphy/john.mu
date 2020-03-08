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
     * Make sure Memcached exists.
     */
    if (!class_exists('\Memcached', true)) {
        throw new Exception('Memcached has not been installed.', E_ERROR);
    }

    /**
     * Using Memcached for our cache management.
     *
     * @package PHY\Cache\Memcached
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Memcached extends \Memcached implements ICache
    {

        /**
         * $settings['id'] will set the persistant_id of this Memcached session.
         *
         * $settings['server'] will try to connect to that server and add pools
         * if an array of servers is sent.
         *
         * @param array $settings
         * @throws Exception If APC caching is not available.
         */
        public function __construct(array $settings = [])
        {
            if (array_key_exists('server', $settings)) {
                $this->addServers($settings['servers']);
            }
        }

        /**
         * Connect to a Memcache server.
         *
         * @param str $host
         * @param int $port
         * @param int $timeout
         */
        public function connect($host, $port = null, $timeout = null)
        {
            foreach ($host as $h) {
                parent::connect($h, $port, $timeout);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function decrement($node, $decrement = 1)
        {
            return parent::decrement($node, $decrement);
        }

        /**
         * {@inheritDoc}
         */
        public function delete($node = false)
        {
            return parent::delete($node);
        }

        /**
         * {@inheritDoc}
         */
        public function flush()
        {
            return parent::flush();
        }

        /**
         * {@inheritDoc}
         */
        public function get($node, $flag = 0)
        {
            return parent::get($node, $flag);
        }

        /**
         * {@inheritDoc}
         */
        public function increment($node, $increment = 1)
        {
            return parent::increment($node, $increment);
        }

        /**
         * {@inheritDoc}
         */
        public function replace($node, $value, $expiration = 0, $flag = 0)
        {
            return parent::replace($node, $value, $flag, $expiration);
        }

        /**
         * {@inheritDoc}
         */
        public function set($node, $value, $expiration = 0, $flag = 0)
        {
            return parent::set($node, $value, $flag, $expiration);
        }

    }
