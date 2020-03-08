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
     * Using Cookie for our cache management. This isn't really advised in general, yet we do use it for our Cookie
     * component to keep things pretty uniformed.
     *
     * @package PHY\Cache\Cookie
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Cookie implements ICache
    {

        protected $prefix = '';

        /**
         * $settings['prefix'] will define a prefix to our cookie storage.
         *
         * @param array $settings
         */
        public function __construct(array $settings = [])
        {
            if (array_key_exists('prefix', $settings)) {
                $this->prefix = $settings['prefix'];
            }
        }

        /**
         * {@inheritDoc}
         */
        public function decrement($node, $decrement = 1)
        {
            $value = $this->get($node);
            if ($value !== false) {
                $value -= $decrement;
                return $this->replace($node, $value);
            } else {
                $value = 0 - $decrement;
                return $this->set($node, $value);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function delete($node)
        {
            unset($_COOKIE[$this->prefix . $node]);
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function flush()
        {
            $_COOKIE = [];
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function get($node, $flag = 0)
        {
            if (is_array($node)) {
                $return = [];
                foreach ($node as $key) {
                    $return[] = $this->get($key, $flag);
                }
                return $return;
            } else if (array_key_exists($this->prefix . $node, $_COOKIE)) {
                return $_COOKIE[$this->prefix . $node];
            }
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function increment($node, $increment = 1)
        {
            $value = $this->get($node);
            if ($value !== false) {
                $value += $increment;
                return $this->replace($node, $value);
            } else {
                $value = $increment;
                return $this->set($node, $value);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function replace($node, $value, $expiration = 0, $flag = 0)
        {
            unset($_COOKIE[$this->prefix . $node]);
            return setcookie($this->prefix . $node, $value, $expiration);
        }

        /**
         * {@inheritDoc}
         */
        public function set($node, $value, $expiration = 0, $flag = 0)
        {
            return setcookie($this->prefix . $node, $value, $expiration);
        }

    }
