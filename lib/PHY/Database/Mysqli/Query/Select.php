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
    use PHY\Database\Query\ISelect;

    /**
     * Our Select classes should all have the same query building functions.
     *
     * @package PHY\Database\Mysqli\Query\Select
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Select extends Element implements ISelect
    {

        protected $select = [];

        /**
         * {@inheritDoc}
         */
        public function reset()
        {
            $this->select = [];
        }

        /**
         * {@inheritDoc}
         */
        public function count($field = '*', $alias = '')
        {
            $alias = $alias && is_string($alias)
                ? $this->clean($alias, '`') . '.'
                : '';
            $field = $field === '*'
                ? '*'
                : $this->clean($field);
            $this->select[] = 'COUNT(' . $alias . $field . ')';
        }

        /**
         * {@inheritDoc}
         */
        public function field($field, $alias = '')
        {
            $alias = $alias && is_string($alias)
                ? $this->clean($alias, true) . '.'
                : '';
            $field = $field === '*'
                ? '*'
                : $this->clean($field, true);
            $this->select[] = $alias . $field;
        }

        /**
         * {@inheritDoc}
         */
        public function max($field = 'id', $alias = '')
        {
            $alias = $alias && is_string($alias)
                ? $this->clean($alias, '`') . '.'
                : '';
            $field = $field === '*'
                ? '*'
                : $this->clean($field);
            $this->select[] = 'MAX(' . $alias . $field . ')';
        }

        /**
         * {@inheritDoc}
         */
        public function min($field = 'id', $alias = '')
        {
            $alias = $alias && is_string($alias)
                ? $this->clean($alias, '`') . '.'
                : '';
            $field = $field === '*'
                ? '*'
                : $this->clean($field);
            $this->select[] = 'MIN(' . $alias . $field . ')';
        }

        /**
         * {@inheritDoc}
         */
        public function raw($raw)
        {
            $this->select[] = $raw;
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            return $this->select;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode($this->select, $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if ($this->select) {
                return ' SELECT ' . join(', ', $this->select) . ' ';
            } else {
                return ' SELECT *';
            }
        }

    }
