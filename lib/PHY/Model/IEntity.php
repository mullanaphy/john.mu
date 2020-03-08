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

    namespace PHY\Model;

    /**
     * Model contracts.
     *
     * @package PHY\Model\IEntity
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IEntity
    {

        /**
         * Initiate the Item class.
         *
         * @param array $data
         */
        public function __construct(array $data = []);

        /**
         * See if this initialize class exists.
         *
         * @return boolean
         */
        public function exists();

        /**
         * Set a key to it's corresponding value if it's allowed
         *
         * @param string $key
         * @param mixed $value
         * @return IEntity
         * @throws Exception
         */
        public function set($key = '', $value = '');

        /**
         * Set our model's initial data.
         *
         * @param array $data
         * @return IEntity
         * @throws Exception
         */
        public function setInitialData(array $data = []);

        /**
         * If key is set you'll get the value back. Otherwise NULL.
         *
         * @param string $key
         * @return mixed
         */
        public function get($key = '');

        /**
         * See if a resource exists.
         *
         * @param string $key
         * @return boolean
         */
        public function has($key);

        /**
         * Return an entity's collection.
         *
         * @return \PHY\Database\ICollection
         */
        public function getCollection();

        /**
         * See if a User doesn't exist or is deleted.
         *
         * @return boolean
         */
        public function isDeleted();

        /**
         * See if this instance is a new row in the Database or not.
         *
         * @return boolean
         */
        public function isNew();

        /**
         * See if this instance's data has been changed.
         *
         * @return boolean
         */
        public function isDifferent();

        /**
         * Get our model's id if it's set.
         *
         * @return string
         */
        public function id();

        /**
         * Get our table's primary key.
         *
         * @return string
         */
        public function getPrimaryKey();

        /**
         * Get an array of settings.
         *
         * @return array
         */
        public function toArray();

        /**
         * Get an array of all changed values.
         *
         * @return array
         */
        public function getChanged();

        /**
         * Get a JSON string of data.
         *
         * @return string JSON encoded values
         */
        public function toJSON();

        /**
         * Get our entity's source (schema).
         *
         * @return array
         */
        public function getSource();

        /**
         * Pre save method call.
         */
        public function preSave();

        /**
         * Post save method call.
         *
         * @param bool $success
         */
        public function postSave($success);

        /**
         * Pre load method call.
         */
        public function preLoad();

        /**
         * Post load method call.
         *
         * @param bool $success
         */
        public function postLoad($success);

        /**
         * Pre delete method call.
         */
        public function preDelete();

        /**
         * Post delete method call.
         *
         * @param bool $success
         */
        public function postDelete($success);

        /**
         * Pre insert method call.
         */
        public function preInsert();

        /**
         * Post insert method call.
         *
         * @param bool $success
         */
        public function postInsert($success);

        /**
         * Pre update method call.
         */
        public function preUpdate();

        /**
         * Post update method call.
         *
         * @param bool $success
         */
        public function postUpdate($success);
    }
