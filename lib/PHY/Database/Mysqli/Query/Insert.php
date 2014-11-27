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
    use PHY\Database\Query\IInsert;

    /**
     * Our Insert classes should all have the same query building functions.
     *
     * @package PHY\Database\Mysqli\Query\Insert
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Insert extends Element implements IInsert
    {

        protected $computed = '';
        protected $keys = [];
        protected $table = '';

        /**
         * {@inheritDoc}
         */
        public function table($table)
        {
            $this->computed = '';
            $this->table = $table;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function add($key)
        {
            $this->computed = '';
            $this->keys[] = $key;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function remove($key)
        {
            $this->computed = '';
            unset($this->keys[array_search($key, $this->keys)]);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function reset()
        {
            $this->computed = '';
            $this->keys = [];
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            return [
                'table' => $this->table,
                'keys' => $this->keys
            ];
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode(['insert' => $this->toArray()], $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if ($this->keys) {
                if (!$this->computed) {
                    $keys = [];
                    $values = [];
                    foreach ($this->keys as $key) {
                        $keys[] = $this->clean($key, true);
                        $values[] = '?';
                    }
                    $this->computed = ' INSERT INTO ' . $this->clean($this->table, true) . ' (' . implode(',', $keys) . ') VALUES (' . implode(',', $values) . ') ';
                }
                return $this->computed;
            } else {
                return ' ';
            }
        }

    }
