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
     * Using APC for our cache management.
     *
     * @package PHY\Cache\Apc
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Apc implements ICache
    {

        /**
         * $settings['mode'] will define what type of APC caching to use.
         *
         * @param array $settings
         * @throws Exception If APC caching is not available.
         */
        public function __construct(array $settings = [])
        {
            if (!isset($settings['mode'])) {
                $settings['mode'] = 'opcode';
            }
            if (!function_exists('apc_cache_info') || !@apc_cache_info($settings['mode'])) {
                throw new Exception('APC Caching is disabled, is not available on this server.', E_USER_WARNING);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function decrement($node, $decrement = 1)
        {
            return apc_dec($node, $decrement);
        }

        /**
         * {@inheritDoc}
         */
        public function delete($node = false)
        {
            return apc_delete($node);
        }

        /**
         * {@inheritDoc}
         */
        public function flush()
        {
            return apc_clear_cache();
        }

        /**
         * {@inheritDoc}
         */
        public function get($node, $flag = 0)
        {
            return apc_fetch($node);
        }

        /**
         * {@inheritDoc}
         */
        public function increment($node, $increment = 1)
        {
            return apc_inc($node, $increment);
        }

        /**
         * {@inheritDoc}
         */
        public function replace($node, $value, $expiration = 0, $flag = 0)
        {
            return apc_store($node, $value, $expiration);
        }

        /**
         * {@inheritDoc}
         */
        public function set($node, $value, $expiration = 0, $flag = 0)
        {
            return apc_add($node, $value, $expiration);
        }

    }
