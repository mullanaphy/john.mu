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
     * Using Disk for our cache management.
     *
     * @package PHY\Cache\Disk
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Disk implements ICache
    {

        protected $location = '';

        /**
         * $settings['location'] will define where to store the cache files.
         *
         * @param array $settings
         * @throws Exception If Disk caching folder doesn't exist or is not writable.
         */
        public function __construct(array $settings = [])
        {
            if (!array_key_exists('location', $settings)) {
                $settings['location'] = '..' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache';
            }
            if (!is_writable($settings['location'])) {
                throw new Exception('Disk Caching is disabled, cache folder is not writable. #' . __LINE__, E_USER_NOTICE);
            }
            $this->location = $settings['location'];
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
            if ($this->location) {
                $file = $this->file($node);
                if (is_writeable($file)) {
                    unlink($file);
                    return true;
                }
            }
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function flush()
        {
            if ($this->location) {
                $DIR = opendir($this->location);
                if ($DIR) {
                    $ignore = ['.', '..'];
                    while (($file = readdir($DIR)) !== false) {
                        if (!in_array($file, $ignore)) {
                            unlink($this->location . DIRECTORY_SEPARATOR . $file);
                        }
                    }
                    return true;
                }
            }
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function get($node, $flag = 0)
        {
            if ($this->location) {
                if (is_array($node)) {
                    $return = [];
                    foreach ($node as $key) {
                        $return[] = $this->get($key, $flag);
                    }
                    return $return;
                } else {
                    $file = $this->file($node);
                    if (is_readable($file)) {
                        $FILE = fopen($file, 'r+');
                        $item = fread($FILE, filesize($file));
                        $item = unserialize($item);
                        fclose($FILE);
                        if (!$item->hasExpired()) {
                            return $item->getContent();
                        } else {
                            $this->delete($item);
                        }
                    }
                }
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
            if ($this->location) {
                $file = $this->file($node);
                if (is_writeable($file)) {
                    unlink($file);
                }
                $FILE = fopen($file, 'w+');
                $_node = new Node($node, $value, $expiration);
                fwrite($FILE, serialize($_node));
                fclose($FILE);
                return $_node->getContent();
            }
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function set($node, $value, $expiration = 0, $flag = 0)
        {
            if ($this->location) {
                $file = $this->file($node);
                if (is_file($file)) {
                    return false;
                }
                $_node = new Node($node, $value, $expiration);
                $FILE = fopen($file, 'w+');
                fwrite($FILE, serialize($_node));
                fclose($FILE);
                return $_node->getContent();
            }
            return false;
        }

        /**
         * Match node names to their appropriate file names.
         *
         * @param string $node
         * @return string
         */
        protected function file($node)
        {
            return $this->location . DIRECTORY_SEPARATOR . md5($node) . '.cache';
        }

    }
