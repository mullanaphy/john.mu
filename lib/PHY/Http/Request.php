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

    namespace PHY\Http;

    /**
     * Handles all the request data.
     *
     * @package PHY\Http\Request
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Request implements IRequest
    {

        protected $controllerName = '';
        protected $actionName = '';
        protected $path = '';
        protected $parameters = [];
        protected $method = 'GET';
        protected $environmentals = [];
        protected static $_defaultEnvironmentals = [
            'REQUEST_METHOD' => 'GET'
        ];
        protected $methods = [];
        protected static $_defaultMethods = ['DELETE', 'GET', 'HEADERS', 'PATCH', 'POST', 'PUT'];
        protected $headers = [];
        protected static $_defaultHeaders = null;

        /**
         * {@inheritDoc}
         */
        public function __construct($path, array $parameters = [], array $environmentals = [], $headers = [])
        {
            $this->path = $path;
            $this->parameters = $parameters;
            $this->setEnvironmentals($environmentals);
            $this->environmentals = array_replace([], $environmentals);
            $this->headers = array_replace(self::getDefaultHeaders(), $headers);
        }

        /**
         * {@inheritDoc}
         */
        public static function createFromGlobal()
        {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                case 'HEAD':
                    $parameters = $_GET;
                    break;
                case 'POST':
                    $parameters = array_merge($_GET, $_POST);
                    break;
                default:
                    parse_str(file_get_contents('php://input'), $parameters);
                    array_merge($_GET, $_POST, $parameters);
                    break;
            }
            $path = $_SERVER['REQUEST_URI'];
            return new static($path, $parameters, array_merge($_ENV, $_SERVER), self::getDefaultHeaders());
        }

        /**
         * {@inheritDoc}
         */
        public function has($key)
        {
            return array_key_exists($key, $this->parameters);
        }

        /**
         * {@=inheritDoc}
         */
        public function get($key, $default = null)
        {
            return array_key_exists($key, $this->parameters)
                ? $this->parameters[$key]
                : $default;
        }

        /**
         * {@inheritDoc}
         */
        public function hasEnvironmental($key)
        {
            return array_key_exists($key, $this->headers);
        }

        /**
         * {@inheritDoc}
         */
        public function getEnvironmental($key, $default = null)
        {
            return array_key_exists($key, $this->environmentals)
                ? $this->environmentals[$key]
                : $default;
        }

        /**
         * {@inheritDoc}
         */
        public function getEnvironmentals()
        {
            return $this->environmentals;
        }

        /**
         * {@inheritDoc}
         */
        public function getDefaultEnvironmentals()
        {
            return self::$_defaultEnvironmentals;
        }

        /**
         * {@inheritDoc}
         */
        public function hasHeader($key)
        {
            return array_key_exists($key, $this->headers);
        }

        /**
         * {@inheritDoc}
         */
        public function getHeader($key, $default = null)
        {
            return array_key_exists($key, $this->headers)
                ? $this->headers[$key]
                : $default;
        }

        /**
         * {@inheritDoc}
         */
        public function getHeaders()
        {
            return $this->headers;
        }

        /**
         * {@inheritDoc}
         */
        public static function getDefaultHeaders()
        {
            if (self::$_defaultHeaders === null) {
                if (function_exists('apache_request_headers')) {
                    self::$_defaultHeaders = apache_request_headers();
                } else {
                    self::$_defaultHeaders = [];
                    $http = '#\AHTTP_#';
                    foreach ($_SERVER as $key => $value) {
                        if (preg_match($http, $key)) {
                            $header = preg_replace($http, '', $key);
                            $matches = explode('_', $header);
                            if (count($matches) > 0 and strlen($header) > 2) {
                                foreach ($matches as $k => $v) {
                                    $matches[$k] = ucfirst(strtolower($v));
                                }
                                $header = implode('-', $matches);
                            }
                            self::$_defaultHeaders[$header] = $value;
                        }
                    }
                }
            }
            return self::$_defaultHeaders;
        }

        /**
         * {@inheritDoc}
         */
        public function getDefaultMethods()
        {
            return self::$_defaultMethods;
        }

        /**
         * {@inheritDoc}
         */
        public function getMethod()
        {
            return $this->method;
        }

        /**
         * {@inheritDoc}
         */
        public function getMethods()
        {
            return $this->methods;
        }

        /**
         * {@inheritDoc}
         */
        public function setControllerName($name)
        {
            return $this->controllerName = $name;
        }

        /**
         * {@inheritDoc}
         */
        public function getControllerName()
        {
            return $this->controllerName;
        }

        /**
         * {@inheritDoc}
         */
        public function setActionName($name)
        {
            return $this->actionName = $name;
        }

        /**
         * {@inheritDoc}
         */
        public function getActionName()
        {
            return $this->actionName;
        }

        /**
         * {@inheritDoc}
         */
        public function getParameters()
        {
            return $this->parameters;
        }

        /**
         * {@inheritDoc}
         */
        public function isMethod($method)
        {
            return $this->getMethod() === strtoupper($method);
        }

        /**
         * {@inheritDoc}
         */
        public function setEnvironmentals(array $environmentals = [])
        {
            $this->environmentals = array_replace($this->getDefaultEnvironmentals(), $environmentals);
            $this->method = strtoupper($this->getEnvironmental('REQUEST_METHOD', 'GET'));
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function setHeaders(array $headers = [])
        {
            $this->headers = array_replace(self::getDefaultHeaders(), $headers);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function setMethods(array $methods = [])
        {
            $this->methods = array_merge($this->getDefaultMethods(), array_map('strtoupper', $methods));
            $this->methods = array_unique($this->methods);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function addParameters(array $parameters = [])
        {
            $this->parameters += $parameters;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function setParameters(array $parameters = [])
        {
            $this->parameters = $parameters;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getRootPath()
        {
            return '';
        }

        /**
         * {@inheritDoc}
         */
        public function getUrl($queryString = false)
        {
            $url = $this->getEnvironmental('REQUEST_URI', '/');
            if (!$queryString && strpos($url, '?') !== false) {
                $url = explode('?', $url)[0];
            }
            return $url;
        }

    }
