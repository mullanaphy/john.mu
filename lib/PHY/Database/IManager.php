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

    use PHY\Cache\ICache;
    use PHY\Model\IEntity;

    /**
     * Contract for managers.
     *
     * @package PHY\Database\IManager
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IManager
    {

        /**
         * Allow the ability to inject a database during initialization.
         *
         * @param IDatabase $database
         */
        public function __construct(IDatabase $database = null);

        /**
         * Inject our database object.
         *
         * @param IDatabase $database
         * @return IManager
         */
        public function setDatabase(IDatabase $database);

        /**
         * Get our database object.
         *
         * @return IDatabase $database
         */
        public function getDatabase();

        /**
         * Set a cache to use with our manager.
         *
         * @param ICache $cache
         * @return IManager
         */
        public function setCache(ICache $cache);

        /**
         * Return our defined cache model for leveraging our load.
         *
         * @return ICache
         */
        public function getCache();

        /**
         * Get a fresh model from our manager.
         *
         * @param string $model
         * @return IEntity $model
         */
        public function getModel($model);

        /**
         * Load a given model from our database and return a usable class.
         *
         * @param mixed $loadBy
         * @param IEntity $model
         * @return IEntity
         */
        public function load($loadBy, IEntity $model);

        /**
         * Save a model to our database.
         *
         * @param IEntity $model
         * @return mixed
         */
        public function save(IEntity $model);

        /**
         * Update an existing model.
         *
         * @param IEntity $model
         * @return boolean
         */
        public function update(IEntity $model);

        /**
         * Insert a model into our database
         *
         * @param IEntity $model
         * @return mixed
         */
        public function insert(IEntity $model);

        /**
         * Delete a model from our database.
         *
         * @param IEntity $model
         * @return boolean
         */
        public function delete(IEntity $model);

        /**
         * Return a query building object.
         *
         * @return IQuery
         */
        public function createQuery();

        /**
         * lean a string for database insertion.
         *
         * @param string
         * @return string
         */
        public function clean($string);

        /**
         * @param string $model
         * @return ICollection
         */
        public function getCollection($model);

        /**
         * Empty all tables associated with a model.
         *
         * @param IEntity $model
         * @return boolean
         */
        public function emptyTable(IEntity $model);

        /**
         * Drop all tables associated with a model.
         *
         * @param IEntity $model
         * @return boolean
         */
        public function dropTable(IEntity $model);
    }
