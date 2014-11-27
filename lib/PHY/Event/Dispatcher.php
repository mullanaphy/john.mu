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

    use PHY\TResources;

    /**
     * For more robust events.
     *
     * @package PHY\Event\Dispatcher
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Dispatcher implements IDispatcher
    {

        use TResources;

        /**
         * {@inheritDoc}
         */
        public function __construct($action = null, $parameters = null, $recurring = null)
        {
            if (is_callable($action)) {
                $this->setAction($action);
            }
            $this->setParameters($parameters);
            $this->setRecurring($recurring);
            return $this;
        }

        /**
         * Get a value for the current dispatcher.
         *
         * @param string $key
         * @return mixed
         */
        public function __get($key)
        {
            if ($this->hasResource($key)) {
                return $this->getResource($key);
            } else {
                return null;
            }
        }

        /**
         * {@inheritDoc}
         */
        public function setAction($action = null)
        {
            if (!is_callable($action)) {
                throw new Exception('Dispatcher actions must be a callable, "' . gettype($action) . '" was provided.');
            }
            $this->setResource('action', $action);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getAction()
        {
            if (!is_callable($this->getResource('action'))) {
                $this->setResource('action', function ($event) {

                });
            }
            return $this->getResource('action');
        }

        /**
         * {@inheritDoc}
         */
        public function setParameters(array $parameters = [])
        {
            $this->setResource('parameters', $parameters);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getParameters()
        {
            return $this->getResource('parameters');
        }

        /**
         * {@inheritDoc}
         */
        public function setRecurring($recurring = false)
        {
            $this->setResource('recurring', (bool)$recurring);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function isRecurring()
        {
            return (bool)$this->getResource('recurring');
        }

        /**
         * {@inheritDoc}
         */
        public function dispatch(IItem $event)
        {
            $event->setDispatcher($this);
            call_user_func_array($this->getAction(), [$event]);
        }

    }
