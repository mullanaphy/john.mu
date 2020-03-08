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

    namespace PHY\Database\Query;

    /**
     * Our Insert classes should all have the same query building functions.
     *
     * @package PHY\Database\IInsert
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IInsert extends IElement
    {

        /**
         * Set the table we're going to save to.
         *
         * @param string $table
         * @return $this
         */
        public function table($table);

        /**
         * Set the data that our insert should be well inserting. If $key is an array then it will append all the data.
         *
         * @param string $key
         * @return $this
         */
        public function add($key);

        /**
         * Unset a key.
         *
         * @param string $key
         * @return $this
         */
        public function remove($key);

        /**
         * Clear out all of our data.
         *
         * @return mixed
         */
        public function reset();
    }
