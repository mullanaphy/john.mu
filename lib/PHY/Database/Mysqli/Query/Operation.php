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

    namespace PHY\Database\Mysqli\Query;

    use PHY\TResources;
    use PHY\Model\IEntity;
    use PHY\Database\IManager;

    /**
     * Abstract class for all Mysqli Query operations.
     *
     * @package PHY\Database\Mysqli\Query\Operation
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    abstract class Operation
    {

        use TResources;

        /**
         * {@inheritDoc}
         */
        public function __toString()
        {
            return $this->toString();
        }

        /**
         * @return string
         */
        public abstract function toString();

        /**
         * Set a manager to use with our objects.
         *
         * @param IManager $manager
         * @return $this
         */
        public function setManager(IManager $manager)
        {
            $this->setResource('manager', $manager);
            return $this;
        }

        /**
         * Return our manager, if none is set, then throw an exception.
         *
         * @return IManager
         * @throws Exception
         */
        public function getManager()
        {
            if (!$this->hasResource('manager')) {
                throw new Exception('Missing a \PHY\Database\IManager object for our operation.');
            }
            return $this->getResource('manager');
        }

        /**
         * Set a model to use with our operation.
         *
         * @param IEntity $model
         * @return $this
         */
        public function setModel(IEntity $model)
        {
            $this->setResource('model', $model);
            return $this;
        }

        /**
         * Get the defined model for our operation.
         *
         * @return IEntity
         * @throws Exception
         */
        public function getModel()
        {
            if (!$this->hasResource('model')) {
                throw new Exception('No model has been set for this operation.');
            }
            return $this->getResource('model');
        }

        /**
         * Grab our model's source.
         *
         * @return array
         */
        public function getSource()
        {
            return $this->getModel()->getSource();
        }

    }
