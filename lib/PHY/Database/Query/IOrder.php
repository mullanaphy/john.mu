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
     * Our Order classes should all have the same query building functions.
     *
     * @package PHY\Database\Query\IOrder
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IOrder extends IElement
    {

        /**
         * What field we should be ordering by.
         *
         * @param string $by
         * @return $this
         */
        public function by($by = 'id');

        /**
         * Which direction we should be sorting by.
         *
         * @param string $direction
         * @return $this
         */
        public function direction($direction = 'asc');

        /**
         * Set a raw field to add.
         *
         * @param string $raw
         * @return $this
         */
        public function raw($raw);
    }
