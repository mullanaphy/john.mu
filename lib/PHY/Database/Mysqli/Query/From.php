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

    use PHY\Database\Query\IFrom;
    use PHY\Database\Mysqli\Query\Element;

    /**
     * Our Mysqli From object.
     *
     * @package PHY\Database\Mysqli\Query\From
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class From extends Element implements IFrom
    {

        protected $string = '';
        protected $alias = false;
        protected $table = [];

        /**
         * {@inheritDoc}
         */
        public function __toString()
        {
            return $this->toString();
        }

        /**
         * {@inheritDoc}
         */
        public function from($table = '', $alias = '')
        {
            $this->string = '';
            $this->table = [
                $alias => [
                    'table' => $table,
                    'alias' => $alias,
                    'on' => ''
                ]
            ];
            $this->alias = $alias;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function innerJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'inner');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function join($type = 'left', $table = '', $alias = false, array $mapping = [])
        {
            $this->string = '';
            if (is_array($alias)) {
                $rightAlias = reset($alias);
                $leftAlias = key($alias);
            } else {
                $leftAlias = $this->alias;
                $rightAlias = $alias
                    ? $alias
                    : $table;
            }
            $alias = $rightAlias;
            if ($rightAlias) {
                $rightAlias = $this->clean($rightAlias, true);
            }
            if ($leftAlias) {
                $leftAlias = $this->clean($leftAlias, true);
            }
            $on = [];
            $mappings = array_slice(func_get_args(), 3);
            foreach ($mappings as $mapping) {
                $ors = [];
                foreach ($mapping as $key => $value) {
                    $ors[] = ($leftAlias
                            ? $leftAlias . '.'
                            : '') . $this->clean($key, true) . ' = ' . ($rightAlias
                            ? $rightAlias . '.'
                            : '') . $this->clean($value, true);
                }
                $on[] = implode(' OR ', $ors);
            }
            $this->table[$alias] = [
                'table' => $table,
                'alias' => $alias,
                'type' => $type,
                'on' => implode(' AND ', $on)
            ];

            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function leftJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'left');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function outerJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'outer');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function rightJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'right');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            return $this->table;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode($this->toArray(), $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if (!$this->string) {
                if ($this->table) {
                    $this->string = ' FROM ';
                    $tables = $this->table;
                    $primary = array_shift($tables);
                    $this->string .= $this->clean($primary['table'], true) . ($this->alias
                            ? ' ' . $this->clean($this->alias, true)
                            : '');
                    foreach ($tables as $alias => $table) {
                        $this->string .= ' ' . strtoupper($table['type']) . ' JOIN ' . $this->clean($table['table'], true) . ' ' . $this->clean($alias, true) . ' ON (' . $table['on'] . ') ';
                    }
                    $this->string .= ' ';
                } else {
                    $this->string = ' ';
                }
            }
            return $this->string;
        }

    }
