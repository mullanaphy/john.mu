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
     * Make sure Memcache exists.
     */
    if (!class_exists('\Memcache', true)) {
        throw new Exception('Memcache has not been installed.', E_ERROR);
    }

    /**
     * Using Memcache for our cache management.
     *
     * @package PHY\Cache\Memcache
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Memcache extends \Memcache implements ICache
    {

        /**
         * $settings['server'] will try to connect to that server.
         *
         * @param array $settings
         * @throws Exception If APC caching is not available.
         */
        public function __construct(array $settings = [])
        {
            if (array_key_exists('server', $settings)) {
                if (is_array($settings['server'])) {
                    $first = false;
                    foreach ($settings['server'] as $server) {
                        if (!$first) {
                            $first = true;
                            call_user_func_array([$this, 'connect'], $server);
                        } else {
                            call_user_func_array([$this, 'addServer'], $server);
                        }
                    }
                } else {
                    $this->connect($settings['server']);
                }
            }
        }

        /**
         * Connect to a Memcache server.
         *
         * @param string $host
         * @param int $port
         * @param int $timeout
         * @return $this
         */
        public function connect($host, $port = null, $timeout = null)
        {
            foreach ($host as $h) {
                parent::connect($h, $port, $timeout);
            }
            return $this;
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
        public function delete($node)
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
            return $value;
        }

        /**
         * {@inheritDoc}
         */
        public function set($node, $value, $expiration = 0, $flag = 0)
        {
            return $value;
        }

    }
