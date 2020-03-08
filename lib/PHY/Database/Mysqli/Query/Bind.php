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
    use PHY\Database\Query\IBind;

    /**
     * Our Bind classes should all have the same query building functions.
     *
     * @package PHY\Database\Mysqli\Query\Bind
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Bind extends Element implements IBind
    {

        protected $computed = null;
        protected $data = [];

        /**
         * {@inheritDoc}
         */
        public function add($value)
        {
            $this->computed = null;
            $this->data[] = $value;
        }

        /**
         * {@inheritDoc}
         */
        public function remove($value)
        {
            $this->computed = null;
            $this->data[] = $value;
        }

        /**
         * {@inheritDoc}
         */
        public function reset()
        {
            $this->computed = null;
            $this->data = [];
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            if ($this->computed === null) {
                $this->computed = [''];
                foreach ($this->data as $value) {
                    switch (gettype($value)) {
                        case 'boolean':
                        case 'integer':
                            $this->computed[0] .= 'i';
                            break;
                        case 'double':
                        case 'float':
                            $this->computed[0] .= 'd';
                            break;
                        case 'array':
                            $value = implode(',', $value);
                        default:
                            $this->computed[0] .= 's';
                    }
                    $this->computed[] = $value;
                }
            }
            return $this->computed;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode(['bind' => $this->toArray()], $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            return '';
        }

    }
