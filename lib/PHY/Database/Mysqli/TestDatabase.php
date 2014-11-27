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

    namespace PHY\Database;

    use PHY\Database\IDatabase;
    use PHY\Database\IManager;

    /**
     * A database for testing purposes.
     *
     * @package PHY\Database\TestDatabase
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class TestDatabase implements IDatabase
    {

        /**
         * {inheritDoc}
         */
        public function __construct(array $settings = array())
        {

        }

        /**
         * {inheritDoc}
         */
        public function __destruct()
        {

        }

        /**
         * {inheritDoc}
         */
        public function clean($string = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function delete($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function hide()
        {

        }

        /**
         * {inheritDoc}
         */
        public function insert($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function last()
        {

        }

        /**
         * {inheritDoc}
         */
        public function multi_free()
        {

        }

        /**
         * {inheritDoc}
         */
        public function multi_query($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function prepare($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function query($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function row($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function select($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function show($show = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function single($sql = false)
        {

        }

        /**
         * {inheritDoc}
         */
        public function update($sql = false)
        {

        }

        /**
         * {@inheritDoc}
         */
        public function setManager(IManager $manager)
        {

        }

        /**
         * {@inheritDoc}
         */
        public function getManager()
        {

        }
    }
