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

    use PHY\Database\IManager;

    /**
     * Contract for all Query elements.
     *
     * @package PHY\Database\Query\IElement
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IElement
    {

        /**
         * Convert our portion of an element block into a query.
         *
         * @return string
         */
        public function __toString();

        /**
         * Set a manager to use with our objects.
         *
         * @param IManager $manager
         * @return IElement
         */
        public function setManager(IManager $manager);

        /**
         * See if a manager has been set on our
         *
         * @return boolean
         */
        public function hasManager();

        /**
         * Return our manager, if none is set then throw an exception.
         *
         * @return IManager
         * @throws Exception
         */
        public function getManager();

        /**
         * Clean scalars and numbers.
         *
         * @param mixed $scalar
         * @return mixed
         */
        public function clean($scalar);
    }
