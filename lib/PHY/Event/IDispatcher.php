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

    namespace PHY\Event;

    /**
     * For more robust events, this is their contract.
     *
     * @package PHY\Event\IDispatcher
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IDispatcher
    {

        /**
         * Create a dispatcher item.
         *
         * @param callable $action Method to be called on dispatch.
         * @param array $parameters Parameters to send along to the method.
         * @param bool $recurring Set true if you want this to be called for every trigger
         */
        public function __construct($action = null, $parameters = null, $recurring = false);

        /**
         * Set the dispatcher action.
         *
         * @param callable $action Method to be called on dispatch.
         * @return Dispatcher
         * @throws Exception
         */
        public function setAction($action = null);

        /**
         * Get the dispatcher action.
         *
         * @return callable
         */
        public function getAction();

        /**
         * Set parameters.
         *
         * @param array $parameters Parameters to send along to the method.
         * @return $this
         */
        public function setParameters(array $parameters = []);

        /**
         * Get parameters.
         *
         * @return array
         */
        public function getParameters();

        /**
         * Set recurring.
         *
         * @param bool $recurring Set true if you want this to be called for every trigger
         * @return $this
         */
        public function setRecurring($recurring = false);

        /**
         * See if we have a recurring dispatcher or not.
         *
         * @return boolean
         */
        public function isRecurring();

        /**
         * Dispatch current item.
         */
        public function dispatch(IItem $event);

    }
