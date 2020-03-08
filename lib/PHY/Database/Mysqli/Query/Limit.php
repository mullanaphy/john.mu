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

    use PHY\Database\Mysqli\Query\Element;
    use PHY\Database\Query\ILimit;

    /**
     * Our Limit classes should all have the same query building functions.
     *
     * @package PHY\Database\Mysqli\Query\Limit
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Limit extends Element implements ILimit
    {

        protected $skip = null;
        protected $limit = null;

        /**
         * {@inheritDoc}
         */
        public function reset()
        {
            $this->skip = null;
            $this->limit = null;
        }

        /**
         * {@inheritDoc}
         */
        public function skip($skip = 0)
        {
            $this->skip = $skip;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function limit($limit = 10)
        {
            $this->limit = $limit;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if ($this->limit !== null) {
                return ' LIMIT ' . ($this->skip !== null
                    ? (int)$this->skip . ','
                    : '') . (int)$this->limit . ' ';
            }
            return ' ';
        }

    }
