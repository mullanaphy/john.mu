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

    use PHY\Event\IDispatcher;
    use PHY\Event\IItem;

    /**
     * Event class for observing and dispatching events.
     *
     * @package PHY\Event
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Event
    {

        private static $_events = [];
        private static $_app;

        /**
         * Set the app for events.
         *
         * @param App $app
         */
        public static function setApp(App $app)
        {
            self::$_app = $app;
        }

        /**
         * Grab our assigned app.
         *
         * @return App
         */
        public static function getApp()
        {
            return self::$_app;
        }

        /**
         * Add an event to trigger list.
         *
         * @param string $event
         * @param IDispatcher $dispatcher\
         * @throws Exception
         */
        public static function on($event, IDispatcher $dispatcher)
        {
            if (!is_string($event)) {
                throw new Exception('First parameter must be a string.');
            }
            if (!array_key_exists($event, self::$_events)) {
                self::$_events[$event] = [];
            }
            self::$_events[$event][] = $dispatcher;
        }

        /**
         * Add an event to trigger list.
         *
         * @param string $event
         * @param IDispatcher $dispatcher
         * @throws Exception
         */
        public static function off($event, IDispatcher $dispatcher = null)
        {
            if (!is_string($event)) {
                throw new Exception('First parameter must be a string.');
            }
            if (array_key_exists($event, self::$_events)) {
                if ($dispatcher === null) {
                    foreach (self::$_events[$event] as $e) {
                        self::$_events[$event][$e] = null;
                        unset(self::$_events[$event][$e]);
                    }
                    unset(self::$_events[$event]);
                } else {
                    foreach (self::$_events[$event] as $e => $d) {
                        if ($d === $dispatcher) {
                            self::$_events[$event][$e] = null;
                            unset(self::$_events[$event][$e]);
                        }
                    }
                }
            }
        }

        /**
         * Get a list of events waiting to be triggered.
         *
         * @param mixed $event
         * @return array
         */
        public static function events($event = null)
        {
            if (is_string($event) && array_key_exists($event, self::$_events)) {
                return self::$_events[$event];
            } else {
                return self::$_events;
            }
        }

        /**
         * Dispatch a trigger.
         *
         * @param string $dispatched
         * @param IItem $event
         */
        public static function dispatch($dispatched = '', IItem $event = null)
        {
            /*
             * If $dispatched is an array, we'll recursively call self::dispatch
             * with the same $event for everyone.
             */
            if (is_array($dispatched)) {
                foreach ($dispatched as $dispatch) {
                    self::dispatch($dispatch, $event);
                }
            } else {
                /*
                 * Otherwise, if an Event\Item is directly passed, we'll grab its
                 * already set name and pass it along.
                 */
                if ($dispatched instanceof Event\Item) {
                    self::dispatch($dispatched->getName(), $dispatched);
                } else {
                    /*
                     * Now we'll actually dispatch our event.
                     */
                    if ($event === null) {
                        $event = new Event\Item;
                    }
                    if (array_key_exists($dispatched, self::$_events)) {
                        $event->setTime(time());
                        $event->setName($dispatched);
                        $event->setChildren(array_key_exists($dispatched, self::$_events)
                            ? count(self::$_events[$dispatched])
                            : 0);
                        $event->setApp(self::getApp());
                        foreach (self::$_events[$dispatched] as $key => $dispatcher) {
                            /**
                             * @var $dispatcher IDispatcher
                             */
                            $dispatcher->dispatch($event);
                            if (!$dispatcher->isRecurring()) {
                                unset(self::$_events[$dispatched][$key]);
                            }
                        }
                    }
                }
            }
        }

    }
