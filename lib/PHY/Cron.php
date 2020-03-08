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
     * Cron routing and handling.
     *
     * @package PHY\Cron
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Cron implements \Iterator, \Countable
    {

        private $tasks = [];

        /**
         * Initiate Cron events.
         *
         * @param array $tasks
         */
        public function __construct(array $tasks = [])
        {
            $this->setTasks($tasks);
        }

        /**
         * Set all tasks that should be run.
         *
         * @param array $tasks
         * @return $this
         */
        public function setTasks(array $tasks = [])
        {
            $this->tasks = $tasks;
            return $this;
        }

        /**
         * Get all set tasks.
         *
         * @return array
         */
        public function getTasks()
        {
            return $this->tasks;
        }

        /**
         * Get the currently set Cron task.
         *
         * @return Cron\Task
         */
        public function current()
        {
            return new Cron\Task((array)current($this->tasks));
        }

        /**
         * Reset our tasks' pointer to the beginning.
         */
        public function rewind()
        {
            reset($this->tasks);
        }

        /**
         * Move our pointer forward one.
         */
        public function next()
        {
            next($this->tasks);
        }

        /**
         * See if there is a task at this pointer.
         *
         * @return boolean
         */
        public function valid()
        {
            return key($this->tasks) !== null;
        }

        /**
         * Grab our currently selected task's key.
         *
         * @return string|number
         */
        public function key()
        {
            return key($this->tasks);
        }

        /**
         * Get a count of tasks.
         *
         * @return int
         */
        public function count()
        {
            return count($this->tasks);
        }

        /**
         * Get the path for a look file to store the lock file.
         *
         * @param string $label
         * @return string
         */
        public static function lock($label = '')
        {
            return 'var' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . md5($label) . '.lock';
        }

    }
