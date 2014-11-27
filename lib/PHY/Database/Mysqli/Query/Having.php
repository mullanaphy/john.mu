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

    /**
     * Our Having classes should all have the same query building functions.
     *
     * @package PHY\Database\Mysqli\Query\Having
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Having extends Element
    {

        protected $having = [];

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            return $this->having;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode(['having' => $this->toArray()], $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if ($this->having) {
                return ' HAVING (' . implode(') AND (', $this->having) . ') ';
            } else {
                return ' ';
            }
        }

        /**
         * {@inheritDoc}
         */
        public function raw($string)
        {
            $this->having[] = $string;
            return $this;
        }

    }
