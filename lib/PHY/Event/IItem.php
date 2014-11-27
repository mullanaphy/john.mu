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
    use PHY\Event\IDispatcher;

    /**
     * Our actual event item that gets pushed along.
     *
     * @package PHY\Event\IItem
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IItem
    {

        /**
         * Inject our event's name and the values to pass along.
         *
         * @param string $name
         * @param array $values
         */
        public function __construct($name = 'event', array $values = []);

        /**
         * Set our event's name.
         *
         * @param string $name
         * @return $this
         */
        public function setName($name = 'event');

        /**
         * Get our event's name.
         *
         * @return string
         */
        public function getName();

        /**
         * Set values.
         *
         * @param array $values
         * @return $this
         */
        public function setValues(array $values = []);

        /**
         * Get our event's values.
         *
         * @return array
         */
        public function getValues();

        /**
         * Set our dispatcher.
         *
         * @param IDispatcher $dispatcher
         * @return $this
         */
        public function setDispatcher(IDispatcher $dispatcher);

        /**
         * Get our assigned dispatcher.
         *
         * @return IDispatcher
         */
        public function getDispatcher();

        /**
         * Set our event's time.
         *
         * @param int $time
         * @return $this
         */
        public function setTime($time = 0);

        /**
         * Get our event's time.
         *
         * @return int
         */
        public function getTime();

        /**
         * Increase our triggered events counter.
         *
         * @return $this
         */
        public function trigger();

        /**
         * Get our triggered events.
         *
         * @return int
         */
        public function getTriggered();

        /**
         * Set our child events.
         *
         * @param int $children
         * @return $this
         */
        public function setChildren($children = 0);

        /**
         * Get our child events.
         *
         * @return int
         */
        public function getChildren();

        /**
         * Inject our app along with our event.
         *
         * @param App $app
         * @return $this
         */
        public function setApp(App $app);

        /**
         * Get our event's app.
         *
         * @return App
         */
        public function getApp();
    }
