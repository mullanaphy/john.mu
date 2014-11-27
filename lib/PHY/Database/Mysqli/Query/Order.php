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
    use PHY\Database\Query\IOrder;

    /**
     * Our Order classes should all have the same query building functions.
     *
     * @package PHY\Database\Mysqli\Query\Order
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Order extends Element implements IOrder
    {

        protected $order = [];
        protected $current = [
            'alias' => 'primary',
            'by' => null,
            'direction' => null
        ];

        /**
         * {@inheritDoc}
         */
        public function by($by = 'id', $alias = 'primary')
        {
            if ($this->current['by']) {
                $this->direction('asc');
            } else if ($this->current['direction'] !== null) {
                $this->order[] = ' ' . $this->clean($alias, true) . '.' . $this->clean($by, true) . ' ' . $this->current['direction'] . ' ';
                $this->current = [
                    'alias' => $alias,
                    'by' => null,
                    'direction' => null
                ];
            } else {
                $this->current['by'] = $by;
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function direction($direction = 'asc')
        {
            if ($this->current['by'] !== null) {
                $this->order[] = ' ' . $this->clean($this->current['alias'], true) . '.' . $this->clean($this->current['by'], true) . ' ' . $direction . ' ';
                $this->current = [
                    'alias' => 'primary',
                    'by' => null,
                    'direction' => null
                ];
            } else {
                $this->current['direction'] = $direction;
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function raw($raw)
        {
            $this->order[] = $raw;
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            return $this->order;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode($this->order, $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if ($this->order || $this->current['by']) {
                if ($this->current['by']) {
                    $this->order[] = ' ' . $this->clean($this->current['alias'], true) . '.' . $this->clean($this->current['by'], true) . ' ' . ($this->current['direction']
                            ? : 'asc') . ' ';
                    $this->current = [
                        'alias' => 'primary',
                        'by' => null,
                        'direction' => null
                    ];
                }
                return ' ORDER BY ' . join(', ', $this->order) . ' ';
            } else {
                return ' ';
            }
        }

    }
