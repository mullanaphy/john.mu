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

    use PHY\TResources;

    /**
     * Generic model handling.
     *
     * @package PHY\Model\Item
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    abstract class Entity implements IEntity
    {

        use TResources;

        protected $data = [];
        protected $initial = [];
        protected $_id = null;
        protected static $_source = [
            'schema' => [
                'primary' => [
                    'table' => 'item',
                    'columns' => [
                        'updated' => 'date',
                        'created' => 'date',
                        'deleted' => 'boolean'
                    ],
                    'id' => 'id'
                ]
            ]
        ];

        /**
         * Initiate the Item class.
         *
         * @param array $data
         */
        public function __construct(array $data = [])
        {
            $this->init($data);
        }

        /**
         * Return a defined key for the user or a count of rows for $key.
         *
         * @param string $key
         * @return mixed
         */
        public function __get($key)
        {
            return $this->get($key);
        }

        /**
         * Store new data. Use in conjunction with
         * Item::store() and Item::save()
         *
         * @param string $key
         * @param mixed $value
         * @return $this
         */
        public function __set($key, $value)
        {
            return $this->set($key, $value);
        }

        /**
         * See if this initialize class exists.
         *
         * @return bool
         */
        public function exists()
        {
            return $this->data && $this->id();
        }

        /**
         * Initialize our model.
         *
         * @param array $data
         * @return $this
         */
        public function init(array $data = [])
        {
            $initial = [];
            $cleaned = [];
            foreach ($this->getSource()['schema'] as $table) {
                foreach ($table['columns'] as $key => $value) {
                    switch ($value) {
                        case 'boolean':
                            if (isset($data[$key])) {
                                $cleaned[$key] = (bool)$data[$key];
                            }
                            $initial[$key] = false;
                            break;
                        case 'id':
                        case 'int':
                        case 'tinyint':
                            if (isset($data[$key])) {
                                $cleaned[$key] = (int)$data[$key];
                            }
                            $initial[$key] = 0;
                            break;
                        case 'decimal':
                        case 'float':
                            if (isset($data[$key])) {
                                $cleaned[$key] = (double)$data[$key];
                            }
                            $initial[$key] = 0.0;
                            break;
                        case 'variable':
                        default:
                            if (isset($data[$key])) {
                                $cleaned[$key] = $data[$key];
                            }
                            $initial[$key] = '';
                            break;
                    }
                }
            }
            $primaryKey = $this->getPrimaryKey();
            if (isset($data[$primaryKey])) {
                $initial[$primaryKey] = (int)$data[$primaryKey];
            }
            $this->initial = $initial;
            $this->data = $initial;
            $this->setInitialData($cleaned);
            return $this;
        }

        /**
         * Set a key to it's corresponding value if it's allowed
         *
         * @param string $key
         * @param mixed $value
         * @return $this
         * @throws Exception
         */
        public function set($key = '', $value = '')
        {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    $this->set($k, $v);
                }
            } else {
                $id = $this->getPrimaryKey();
                if ($key === $id) {
                    $this->data[$id] = $value;
                } else if (!array_key_exists($key, $this->data)) {
                    throw new Exception(get_class($this) . ' does not have a key "' . $key . '" defined. Defined keys: "' . join('", "', array_keys($this->data)) . '"');
                } else {
                    if ($this->data[$key] !== $value) {
                        $this->data[$key] = $value;
                    }
                }
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function setInitialData(array $data = [])
        {
            $this->set($data);
            $this->initial = $this->data;
            return $this;
        }

        /**
         * If key is set you'll get the value back. Otherwise NULL.
         *
         * @param string $key
         * @return mixed
         */
        public function get($key = '')
        {
            if (array_key_exists($key, $this->data)) {
                return $this->data[$key];
            } else {
                return null;
            }
        }

        /**
         * See if a resource exists.
         *
         * @param string $key
         * @return bool
         */
        public function has($key)
        {
            return array_key_exists($key, $this->data);
        }

        /**
         *
         * @return Collection
         */
        public function getCollection()
        {
            $collection = get_class($this) . '\\Collection';
            $collection = new $collection;
            return $collection;
        }

        /**
         * See if a User doesn't exist or is deleted.
         *
         * @return bool
         */
        public function isDeleted()
        {
            return !$this->exists() || !array_key_exists('deleted', $this->data) || $this->data['deleted'];
        }

        /**
         * See if this instance is a new row in the Database or not.
         *
         * @return bool
         */
        public function isNew()
        {
            return !$this->id();
        }

        /**
         * See if this instance's data has been changed.
         *
         * @return bool
         */
        public function isDifferent()
        {
            return $this->initial !== $this->data;
        }

        /**
         * Get our model's id if it's set.
         *
         * @return string
         */
        public function id()
        {
            $id = $this->getPrimaryKey();
            return array_key_exists($id, $this->data)
                ? $this->data[$id]
                : false;
        }

        /**
         * Get our model's id key.
         *
         * @return string
         */
        public function getPrimaryKey()
        {
            if ($this->_id === null) {
                $source = $this->getSource();
                $id = array_key_exists('id', $source['schema']['primary'])
                    ? $source['schema']['primary']['id']
                    : 'id';
                $this->_id = $id;
            }
            return $this->_id;
        }

        /**
         * Get an array of settings.
         *
         * @return array
         */
        public function toArray()
        {
            return $this->data;
        }

        /**
         * Get an array of all changed values.
         *
         * @return array
         */
        public function getChanged()
        {
            if (!$this->exists()) {
                return $this->data;
            }
            if (!$this->isDifferent()) {
                return [];
            }
            $changed = [];
            $primaryKey = $this->getPrimaryKey();
            foreach ($this->data as $key => $value) {
                if ($key === $primaryKey) {
                    continue;
                } else if ($value !== $this->initial[$key]) {
                    $changed[$key] = $value;
                }
            }
            return $changed;
        }

        /**
         * Get a JSON string of data.
         *
         * @return string JSON encoded values
         */
        public function toJSON()
        {
            return json_encode($this->toArray());
        }

        /**
         * Get our entity's source (schema).
         *
         * @return array
         */
        public function getSource()
        {
            return static::$_source;
        }

        /**
         * {@inheritDoc}
         */
        public function preLoad()
        {

        }

        /**
         * {@inheritDoc}
         */
        public function postLoad($success)
        {

        }

        /**
         * {@inheritDoc}
         */
        public function preSave()
        {

        }

        /**
         * {@inheritDoc}
         */
        public function postSave($success)
        {

        }

        /**
         * {@inheritDoc}
         */
        public function preDelete()
        {

        }

        /**
         * {@inheritDoc}
         */
        public function postDelete($success)
        {

        }

        /**
         * {@inheritDoc}
         */
        public function preInsert()
        {

        }

        /**
         * {@inheritDoc}
         */
        public function postInsert($success)
        {

        }

        /**
         * {@inheritDoc}
         */
        public function preUpdate()
        {

        }

        /**
         * {@inheritDoc}
         */
        public function postUpdate($success)
        {

        }
    }

