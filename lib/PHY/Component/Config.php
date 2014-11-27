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

    namespace PHY\Component;

    /**
     * Config namespace
     *
     * @package PHY\Component\Config
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Config extends AComponent
    {

        /**
         * {@inheritDoc}
         */
        public function get($key)
        {
            $namespace = $this->getNamespace();
            $values = explode('/', $key);
            $key = array_shift($values);
            if (!array_key_exists($namespace, $this->resources)) {
                $this->resources[$namespace] = [];
            }
            if (!array_key_exists($key, $this->resources[$namespace])) {
                $file = false;
                $paths = $this->getApp()->getPath()
                    ->getPaths('config' . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $key . '.json', 'config' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $key . '.json');
                foreach ($paths as $check) {
                    if (is_readable($check)) {
                        $file = $check;
                        break;
                    }
                }
                if (!$file) {
                    throw new Exception('Config "' . $key . '" was not found.');
                }
                $FILE = fopen($file, 'r');
                $content = fread($FILE, filesize($file));
                fclose($FILE);
                $content = preg_replace('#/\*.+?\*/#is', '', $content);
                $this->resources[$namespace][$key] = json_decode($content, JSON_OBJECT_AS_ARRAY);
            }
            if ($values) {
                $temp = $this->resources[$namespace][$key];
                foreach ($values as $value) {
                    if (!array_key_exists($value, $temp)) {
                        return null;
                    } else {
                        if ($temp) {
                            $temp = $temp[$value];
                        }
                    }
                }
                return $temp;
            } else {
                return $this->resources[$namespace][$key];
            }
        }

    }
