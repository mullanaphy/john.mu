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

    use PHY\App;

    /**
     * Component interface
     *
     * @package PHY\Component\IComponent
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IComponent
    {

        /**
         * We want our component to allow for our App to be injected on
         * initialization.
         *
         * @param App $app
         */
        public function __construct(App $app = null);

        /**
         * Set the global App so this component can reference it if need be.
         *
         * @param App $app
         * @return $this
         */
        public function setApp(App $app);

        /**
         * Grab our defined App.
         *
         * @return App
         */
        public function getApp();

        /**
         * Name of our Component
         *
         * @return string
         */
        public function getName();

        /**
         * Delete a key from our component.
         *
         * @param string $key
         * @return boolean
         */
        public function delete($key);

        /**
         * Get a key from our component.
         *
         * @param string $key
         * @return mixed
         */
        public function get($key);

        /**
         * See if our component has a given key.
         *
         * @param string $key
         * @param boolean
         */
        public function has($key);

        /**
         * Set a key for our compontent.
         *
         * @param string $key
         * @param mixed $value
         * @param boolean
         */
        public function set($key, $value);
    }
