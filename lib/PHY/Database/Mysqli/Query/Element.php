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

    use PHY\Database\Query\IElement;
    use PHY\Database\IManager;
    use PHY\Model\IEntity;

    /**
     * Abstract class for all Mysqli Query elements.
     *
     * @package PHY\Database\Mysqli\Query\Element
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    abstract class Element implements IElement
    {

        private $manager = null;
        private $model = null;

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
         * {@inheritDoc}
         */
        public function setManager(IManager $manager)
        {
            $this->manager = $manager;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function hasManager()
        {
            return $this->manager !== null;
        }

        /**
         * {@inheritDoc}
         */
        public function getManager()
        {
            if (!$this->hasManager()) {
                throw new Exception('Missing a \PHY\Database\IManager object for our element.');
            }
            return $this->manager;
        }

        /**
         * {@inheritDoc}
         */
        public function setModel(IEntity $model)
        {
            $this->model = $model;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function hasModel()
        {
            return $this->model !== null;
        }

        /**
         * {@inheritDoc}
         */
        public function getModel()
        {
            if (!$this->hasModel()) {
                throw new Exception('Missing a \PHY\Model\Entity object for our element.');
            }
            return $this->manager;
        }

        /**
         * {@inheritDoc}
         */
        public function clean($scalar, $column = false)
        {
            if ($column) {
                $quotes = '`';
            } else {
                $quotes = "'";
            }
            switch (gettype($scalar)) {
                case 'float':
                case 'double':
                    return (float)$scalar;
                    break;
                case 'int':
                    return (int)$scalar;
                default:
                    return is_numeric($scalar)
                        ? (int)$scalar
                        : $quotes . $this->getManager()->clean($scalar) . $quotes;
            }
        }

    }
