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
    use PHY\Database\Query\IWhere;

    /**
     * Our Where classes should all have the same query building functions.
     *
     * @package PHY\Database\Mysqli\Query\Where
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Where extends Element implements IWhere
    {

        protected $current = [];

        /**
         * {@inheritDoc}
         */
        public function also($field, $alias = false)
        {
            $location = $this->location();
            $this->current[$location[0]][] = [
                'field' => $this->clean($field, true),
                'alias' => $alias
                    ? $this->clean($alias, true)
                    : false,
                'value' => null,
                'or' => false
            ];
        }

        /**
         * {@inheritDoc}
         */
        public function field($field, $alias = false)
        {
            $this->current[] = [
                [
                    'field' => $this->clean($field, true),
                    'alias' => $alias
                        ? $this->clean($alias, true)
                        : false,
                    'value' => null,
                    'or' => false
                ]
            ];
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function gt($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' > ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function gte($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' >= ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function in(array $value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' IN (' . implode(',', array_map([
                    $this,
                    'clean'
                ], $value)) . ")";
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function notIn(array $value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' NOT IN (' . implode(',', array_map([
                    $this,
                    'clean'
                ], $value)) . ")";
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function instead($field, $alias = false)
        {
            $location = $this->location();
            $this->current[$location[0]][] = [
                'field' => $this->clean($field, true),
                'alias' => $alias
                    ? $this->clean($alias, true)
                    : false,
                'value' => null,
                'or' => true
            ];
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function is($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' = ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function like($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' LIKE ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function lt($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' < ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function lte($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' <= ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function not($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' != ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function notLike($value)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' NOT LIKE ' . $this->clean($value);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function range($start, $finish)
        {
            $this->throwExceptionForImproperChaining();
            $location = $this->location();
            $this->current[$location[0]][$location[1]]['value'] = ' BETWEEN(' . $this->clean($start) . ',' . $this->clean($finish) . ')';
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            $complete = [];
            foreach ($this->current as $group) {
                if (is_array($group)) {
                    $first = array_shift($group);
                    $set = ($first['alias']
                            ? $first['alias'] . '.'
                            : '') . $first['field'] . $first['value'];
                    foreach ($group as $part) {
                        $field = ($part['alias']
                                ? $part['alias'] . '.'
                                : '') . $part['field'];
                        $set .= ' ' . ($part['or']
                                ? 'OR'
                                : 'AND') . ' ' . $field . $part['value'];
                    }
                } else {
                    $set = $group;
                }
                $complete[] = $set;
            }
            return $complete;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode(['where' => $this->toArray()], $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if ($this->current) {
                return ' WHERE (' . implode(') AND (', $this->toArray()) . ') ';
            } else {
                return ' ';
            }
        }

        /**
         * {@inheritDoc}
         */
        protected function checkForField()
        {
            $location = $this->location();
            return (bool)$this->current[$location[0]][$location[1]]['field'];
        }

        /**
         * {@inheritDoc}
         */
        protected function checkForValue()
        {
            $location = $this->location();
            return $this->current[$location[0]][$location[1]]['value'] !== null;
        }

        /**
         * {@inheritDoc}
         */
        protected function throwExceptionForImproperChaining()
        {
            if (!$this->checkForField()) {
                throw new Exception('Cannot chain a matching based method without a defined field.');
            } else {
                if ($this->checkForValue()) {
                    throw new Exception('We already have a match set, please set another field.');
                }
            }
        }

        protected function location()
        {
            $outer = count($this->current) - 1;
            $inner = count($this->current[$outer]) - 1;
            return [$outer, $inner];
        }

        /**
         * {@inheritDoc}
         */
        public function raw($string)
        {
            $this->current[] = $string;
            return $this;
        }

    }
