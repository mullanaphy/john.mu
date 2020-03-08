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

    use PHY\App;

    /**
     * Our actual event item that gets pushed along.
     *
     * @package PHY\Event\Item
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Item implements IItem
    {

        protected $app;
        protected $name = 'event';
        protected $values = [];
        protected $dispatcher;
        protected $time = 0;
        protected $children = 0;
        protected $triggered = 0;

        /**
         * {@inheritDoc}
         */
        public function __construct($name = 'event', array $values = [])
        {
            $this->setName($name);
            $this->setValues($values);
        }

        /**
         * Get a defined value.
         *
         * @param string $key
         * @return mixed
         */
        public function __get($key)
        {
            if (array_key_exists($key, $this->values)) {
                return $this->values[$key];
            } else {
                return null;
            }
        }

        /**
         * {@inheritDoc}
         */
        public function setName($name = 'event')
        {
            $this->name = $name;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * {@inheritDoc}
         */
        public function setValues(array $values = [])
        {
            $this->values = $values;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getValues()
        {
            return $this->values;
        }

        /**
         * {@inheritDoc}
         */
        public function setDispatcher(IDispatcher $dispatcher)
        {
            $this->dispatcher = $dispatcher;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getDispatcher()
        {
            return $this->dispatcher;
        }

        /**
         * {@inheritDoc}
         */
        public function setTime($time = 0)
        {
            $this->time = $time;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getTime()
        {
            return $this->time;
        }

        /**
         * {@inheritDoc}
         */
        public function trigger()
        {
            ++$this->triggered;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getTriggered()
        {
            return $this->triggered;
        }

        /**
         * {@inheritDoc}
         */
        public function setChildren($children = 0)
        {
            $this->children = $children;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getChildren()
        {
            return $this->children;
        }

        /**
         * {@inheritDoc}
         */
        public function setApp(App $app)
        {
            $this->app = $app;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getApp()
        {
            return $this->app;
        }
    }
