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

    namespace PHY\Model\Authorize;

    use PHY\Model\Collection as BaseCollection;
    use PHY\Model\User;

    /**
     * Authorization collection.
     *
     * @package PHY\Model\Authorize\Collcetion
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Collection extends BaseCollection
    {

        protected static $_source = '\PHY\Model\Authorize';

        /**
         * Set up what User to use along side this Collection.
         * If none is provided then Modules will be all willy-nilly.
         *
         * @param User $User
         * @return $this
         */
        public function setUser(User $User)
        {
            $this->setResource('user', $User);
            return $this;
        }

        /**
         * Get a defined user.
         *
         * @return User
         */
        public function getUser()
        {
            return $this->getResource('user');
        }

    }
