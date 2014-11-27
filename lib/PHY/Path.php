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

    namespace PHY;

    /**
     * Get the appropriate routing for paths.
     *
     * @package PHY\Path
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     * @static
     */
    class Path
    {

        protected $routes = [];

        /**
         * You can inject default routes on load.
         *
         * @param array $routes
         */
        public function __construct(array $routes = [])
        {
            $this->addRoute($routes);
        }

        /**
         * Add a new route and it's path. You can send key => value array of
         * multiple routes to add.
         *
         * @param mixed $route
         * @param string $path
         * @return $this
         */
        public function addRoute($route = '', $path = '')
        {
            if (is_array($route)) {
                foreach ($route as $key => $path) {
                    $this->addRoute($key, $path);
                }
            } else {
                $this->routes[$route] = $path;
            }
            return $this;
        }

        /**
         * Get an array with all combos of paths to routes.
         *
         * @param string [,...] $path
         * @return array
         */
        public function getPaths()
        {
            $sources = func_get_args();
            $routes = $this->getRoutes();
            $paths = [];
            foreach ($sources as $source) {
                if (is_array($source)) {

                }
                foreach ($routes as $route => $path) {
                    $paths[$route . DIRECTORY_SEPARATOR . $source] = $path . $source;
                }
            }
            return $paths;
        }

        /**
         * Get our current list of routes.
         *
         * @param string [,...] $route
         * @return array
         */
        public function getRoutes()
        {
            if (func_num_args()) {
                $routes = [];
                foreach (func_get_args() as $route) {
                    if (array_key_exists($route, $this->routes)) {
                        $routes[$route] = $this->routes[$route];
                    }
                }
                return $routes;
            } else {
                return $this->routes;
            }
        }

    }
