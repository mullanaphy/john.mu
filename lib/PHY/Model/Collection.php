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

    namespace PHY\Model;

    use PHY\Database\ICollection;
    use PHY\Database\IManager;

    /**
     * Grab collections of our precious models.
     *
     * @package PHY\Model\Collection
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Collection implements ICollection
    {

        protected $count = null;
        protected $items = null;
        protected $manager = null;
        protected $query = null;
        protected $model = null;
        protected $raw = false;
        protected static $_source = null;

        /**
         * Set our manager used for our given collection.
         *
         * @param IManager $manager
         * @return $this
         */
        public function setManager(IManager $manager)
        {
            if (static::$_source) {
                $model = static::$_source;
            } else {
                $model = explode('\\', get_class($this));
                array_pop($model);
                $model = implode('\\', $model);
            }
            $this->model = new $model;
            $this->manager = $manager;
            return $this;
        }

        /**
         * Get our defined manager.
         *
         * @return IManager
         */
        public function getManager()
        {
            return $this->manager;
        }

        /**
         * Initialize our Query if one hasn't already been set.
         *
         * @return $this
         */
        public function getQuery()
        {
            if ($this->query === null) {
                $this->query = $this->getManager()->createQuery()->selectFromModel($this->model);
            }
            return $this->query;
        }

        /**
         * {@inheritDoc}
         */
        public function count()
        {
            if ($this->count === null) {
                if ($this->items !== null) {
                    $this->count = count($this->items);
                } else {
                    $query = clone $this->getQuery();
                    $select = $query->get('select');
                    $select->reset();
                    $select->count();
                    $limit = $query->get('limit');
                    $limit->reset();
                    $result = $query->execute()->getIterator();
                    if ($result) {
                        $this->count = (int)$result->fetch_array()[0];
                    } else {
                        $this->count = 0;
                    }
                }
            }
            return $this->count;
        }

        /**
         * Return an entity
         *
         * @return IEntity
         */
        public function current()
        {
            $current = current($this->items);
            if ($this->raw) {
                return $current;
            } else {
                $class = static::$_source;
                return new $class($current);
            }
        }

        /**
         * Move our collection pointer forward once.
         */
        public function next()
        {
            next($this->items);
        }

        /**
         * {@inheritDoc}
         */
        public function from()
        {
            return $this->getQuery()->get('from');
        }

        /**
         * Load our actual collection and populate self::$items with the grabbed
         * data.
         */
        public function load()
        {
            if ($this->items === null) {
                $query = $this->getQuery()->execute();
                foreach ($query as $item) {
                    $this->items[] = $item;
                }
                if (!$this->items) {
                    $this->items = [];
                }
            }
        }

        /**
         * {@inheritDoc}
         */
        public function key()
        {
            return key($this->items);
        }

        /**
         * {@inheritDoc}
         */
        public function map($function)
        {
            $this->load();
            return array_map($function, $this->items);
        }

        /**
         * {@inheritDoc}
         */
        public function order()
        {
            return $this->getQuery()->get('order');
        }

        /**
         * {@inheritDoc}
         */
        public function raw($raw = false)
        {
            $this->raw = $raw;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function reduce($function, $default = [])
        {
            return array_reduce($this->items, $function, $default);
        }

        /**
         * Reset our pointer to the first item on our collection.
         */
        public function rewind()
        {
            $this->load();
            reset($this->items);
        }

        /**
         * {@inheritDoc}
         */
        public function select()
        {
            return $this->getQuery()->get('select');
        }

        /**
         * Return true if this current pointer exists.
         *
         * @return boolean
         */
        public function valid()
        {
            return key($this->items) !== null;
        }

        /**
         * {@inheritDoc}
         */
        public function where()
        {
            return $this->getQuery()->get('where');
        }

        /**
         * {@inheritDoc}
         */
        public function limit($skip = 0, $limit = null)
        {
            if ($limit === null) {
                $this->getQuery()->get('limit')->limit($skip);
            } else {
                $this->getQuery()->get('limit')->skip($skip);
                $this->getQuery()->get('limit')->limit($limit);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            $this->rewind();
            $items = [];
            foreach ($this as $item) {
                $items[] = $item;
            }
            return $items;
        }
    }
