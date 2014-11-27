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
     * Request contract.
     *
     * @package PHY\Http\IRequest
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IRequest
    {

        /**
         * Set default values.
         *
         * @param string $path
         * @param array $parameters
         * @param array $environmentals
         * @param array $headers
         */
        public function __construct($path, array $parameters = [], array $environmentals = [], $headers = []);

        /**
         * Create a new Request using global variables.
         *
         * @return IRequest
         */
        public static function createFromGlobal();

        /**
         * Return whether a REQUEST parameter exists or not.
         *
         * @param string $key
         * @return bool
         */
        public function has($key);

        /**
         * Return a value from the REQUEST if it exists.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed|null
         */
        public function get($key, $default = null);

        /**
         * Return whether an environmental exists or not.
         *
         * @param string $key
         * @return bool
         */
        public function hasEnvironmental($key);

        /**
         * Return a value from our environmentals if it exists.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed|null
         */
        public function getEnvironmental($key, $default = null);

        /**
         * Get currently defined environnmentals.
         *
         * @return array
         */
        public function getEnvironmentals();

        /**
         * Get default environmentals.
         *
         * @return array
         */
        public function getDefaultEnvironmentals();

        /**
         * Return whether a header exists or not.
         *
         * @param string $key
         * @return bool
         */
        public function hasHeader($key);

        /**
         * Return a value from our headers if it exists.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed|null
         */
        public function getHeader($key, $default = null);

        /**
         * Get currently defined headers.
         *
         * @return array
         */
        public function getHeaders();

        /**
         * Get default headers.
         *
         * @return array
         * @static
         */
        public static function getDefaultHeaders();

        /**
         * Get default methods.
         *
         * @return array
         */
        public function getDefaultMethods();

        /**
         * Return the current request method.
         *
         * @return string
         */
        public function getMethod();

        /**
         * Get allowed methods.
         *
         * @return array
         */
        public function getMethods();

        /**
         * Returns an array of allowed request method calls.
         *
         * @return array
         * @static
         */
        public function getParameters();

        /**
         * See if our request is a given method.
         *
         * @param string $method
         * @return boolean
         */
        public function isMethod($method);

        /**
         * Set environmentals.
         *
         * @param array $environmentals
         * @return IRequest
         */
        public function setEnvironmentals(array $environmentals = []);

        /**
         * Set headers.
         *
         * @param array $headers
         * @return IRequest
         */
        public function setHeaders(array $headers = []);

        /**
         * Set allowed methods.
         *
         * @param array $methods
         * @return IRequest
         */
        public function setMethods(array $methods = []);

        /**
         * Add some parameters.
         *
         * @param array $parameters
         * @return IRequest
         */
        public function addParameters(array $parameters = []);

        /**
         * Set parameters.
         *
         * @param array $parameters
         * @return IRequest
         */
        public function setParameters(array $parameters = []);

        /**
         * Get the request's root path.
         *
         * @return string
         */
        public function getRootPath();

        /**
         * Get the request's url.
         *
         * @return string
         */
        public function getUrl();

        /**
         * Set our desired controller's name.
         *
         * @param string $name
         * @return IRequest
         */
        public function setControllerName($name);

        /**
         * Get our desired controller's name.
         *
         * @return string
         */
        public function getControllerName();

        /**
         * Set our desired action's name.
         *
         * @param string $name
         * @return IRequest
         */
        public function setActionName($name);

        /**
         * Get our desired action's name.
         *
         * @return string
         */
        public function getActionName();
    }
