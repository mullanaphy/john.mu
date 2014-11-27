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

    use PHY\Model\IEntity;

    /**
     * All query based classes must have a generate ability.
     *
     * @package PHY\Database\IQuery
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    interface IQuery extends \IteratorAggregate
    {

        /**
         * Inject our manager while loading.
         *
         * @param IManager $manager
         */
        public function __construct(IManager $manager);

        /**
         * Render our object down to a string if we can.
         *
         * @return string
         */
        public function __toString();

        /**
         * Set our manager.
         *
         * @param IManager $manager
         * @return $this
         */
        public function setManager(IManager $manager);

        /**
         * Get our manager.
         *
         * @return IManager
         */
        public function getManager();

        /**
         * Render our object down to an if we can.
         *
         * @return array
         */
        public function toArray();

        /**
         * Render our object down to a string if we can.
         *
         * @return string
         */
        public function toString();

        /**
         * Render our object down to a JSON string if we can.
         *
         * @param int $flags
         * @return string JSON
         */
        public function toJSON($flags = 0);

        /**
         * Execute our query.
         *
         * @return IQuery
         */
        public function execute();

        /**
         * Create a SELECT query based on a model.
         *
         * @param IEntity $model
         * @return $this
         */
        public function selectFromModel(IEntity $model);

        /**
         * Create a DELETE query based on a model.
         *
         * @param IEntity $model
         * @return $this
         */
        public function deleteFromModel(IEntity $model);

        /**
         * Create an INSERT query based on a model.
         *
         * @param IEntity $model
         * @return $this
         */
        public function insertFromModel(IEntity $model);

        /**
         * Create an UPDATE query based on a model.
         *
         * @param IEntity $model
         * @return $this
         */
        public function updateFromModel(IEntity $model);

        /**
         * Create an UPSERT query based on a model.
         *
         * @param IEntity $model
         * @return $this
         */
        public function upsertFromModel(IEntity $model);

    }
