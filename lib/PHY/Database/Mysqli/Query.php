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

    namespace PHY\Database\Mysqli;

    use PHY\Database\IQuery;
    use PHY\Database\IManager;
    use PHY\Model\IEntity;

    /**
     * Our main Query element. This is in essence our query builder.
     *
     * @package PHY\Database\Mysqli\Query
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Query extends Query\Element implements IQuery
    {

        private $elements = [];
        private $results = null;
        private $string = '';

        public function __clone()
        {
            foreach ($this->elements as $key => $element) {
                $this->elements[$key] = clone $element;
            }
            $this->results = null;
            $this->string = '';
        }

        /**
         * {@inheritDoc}
         */
        public function __construct(IManager $manager = null, IEntity $model = null)
        {
            if ($manager !== null) {
                $this->setManager($manager);
            }
            if ($model !== null) {
                $this->setModel($model);
            }
        }

        /**
         * Grab a portion of our query.
         *
         * @param string $object
         * @return \PHY\Database\Query\IElement
         * @throws Exception
         */
        public function get($object)
        {
            if ($this->elements === null) {
                $this->select();
            }
            if ($this->has($object)) {
                if (is_object($this->elements[$object])) {
                    return $this->elements[$object];
                } else {
                    throw new Exception('"' . $object . '" is not an object... I am blaming you...');
                }
            } else {
                throw new Exception('"' . $object . '" is undefined. Available calls are "' . implode('", "', array_keys($this->elements)) . '".');
            }
        }

        /**
         * Let us know if this query has a specific element.
         *
         * @param string $object
         * @return bool
         */
        public function has($object)
        {
            return array_key_exists($object, $this->elements);
        }

        /**
         * Return an initialized block element of our query.
         *
         * @param string $key
         * @return \PHY\Database\Query\IElement
         */
        public function __get($key)
        {
            return $this->get($key);
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            return $this->elements;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode($this->elements, $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if (!$this->string) {
                $this->string = implode(' ', $this->elements);
            }
            return $this->string;
        }

        /**
         * {@inheritDoc}
         */
        public function execute()
        {
            if ($this->results === null) {
                $database = $this->getManager()->getDatabase();
                if (!trim($this->toString())) {
                    $this->results = [];
                    return $this;
                }
                if ($this->has('bind')) {
                    $prepare = $database->prepare($this->toString());
                    $bind = $this->get('bind')->toArray();
                    $bound = [];
                    foreach ($bind as $key => $value) {
                        if ($key > 0) {
                            $bound[$key] = &$bind[$key];
                        } else {
                            $bound[$key] = $value;
                        }
                    }
                    call_user_func_array([$prepare, 'bind_param'], $bound);
                    $this->results = $prepare->execute();
                } else {
                    $this->results = $database->query($this->toString());
                }
                if ($database->error) {
                    throw new Exception($database->error);
                }
                if (!$this->results) {
                    $this->results = [];
                }
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getIterator()
        {
            $this->execute();
            return $this->results;
        }

        /**
         * {@inheritDoc}
         */
        public function setManager(IManager $manager)
        {
            parent::setManager($manager);
            foreach ($this->elements as $element) {
                /* @var Query\Element $element */
                $element->setManager($manager);
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function setModel(IEntity $model)
        {
            parent::setModel($model);
            foreach ($this->elements as $element) {
                /* @var Query\Element $element */
                $element->setModel($model);
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function select()
        {
            $this->elements = [
                'select' => new Query\Select,
                'from' => new Query\From,
                'where' => new Query\Where,
                'having' => new Query\Having,
                'order' => new Query\Order,
                'limit' => new Query\Limit
            ];
            $this->injectManagerAndModel();
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function update()
        {
            $this->elements = [
                'update' => new Query\Update,
                'where' => new Query\Where,
                'limit' => new Query\Limit,
                'bind' => new Query\Bind
            ];
            $this->injectManagerAndModel();
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function delete()
        {
            $this->elements = [
                'delete' => new Query\Delete,
                'from' => new Query\From,
                'where' => new Query\Where,
                'limit' => new Query\Limit
            ];
            $this->injectManagerAndModel();
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function insert()
        {
            $this->elements = [
                'insert' => new Query\Insert,
                'bind' => new Query\Bind
            ];
            $this->injectManagerAndModel();
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function upsert()
        {
            $this->elements = [
                'upsert' => new Query\Upsert,
                'limit' => new Query\Limit,
                'bind' => new Query\Bind
            ];
            $this->injectManagerAndModel();
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function selectFromModel(IEntity $model)
        {
            $this->select();
            $this->setModel($model);
            /* @var Query\From $from */
            $from = $this->get('from');
            /* @var Query\Select $select */
            $select = $this->get('select');
            $source = $model->getSource();
            $id = $model->getPrimaryKey();
            foreach ($source['schema'] as $alias => $table) {
                if ($alias === 'primary') {
                    $from->from($table['table'], $alias);
                    $select->field($id, $alias);
                } else {
                    $joined = false;
                    if (array_key_exists('mapping', $table)) {
                        $from->leftJoin($table['table'], $alias, $table['mapping']);
                        $joined = true;
                    } else if (array_key_exists('keys', $table) && array_key_exists('foreign', $table['keys'])) {
                        foreach ($table['keys']['foreign'] as $key => $meta) {
                            if (is_array($meta)) {
                                if ($meta['table'] === 'primary') {
                                    $from->leftJoin($table['table'], $alias, [
                                        $meta['key'] => $key
                                    ]);
                                    $joined = true;
                                    break;
                                }
                            } else {
                                if ($meta['table'] === 'primary' && $meta === $id) {
                                    $from->leftJoin($table['table'], $alias, [
                                        $id => $key
                                    ]);
                                    $joined = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (!$joined) {
                        $from->leftJoin($table['table'], $alias, [
                            $id => array_key_exists('id', $table)
                                ? $table['id']
                                : 'id'
                        ]);
                    }
                }
                foreach ($table['columns'] as $column => $meta) {
                    $select->field($column, $alias);
                }
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function deleteFromModel(IEntity $model, $alias = 'primary')
        {
            $this->delete();
            $this->setModel($model);
            /* @var Query\From $from */
            $from = $this->get('from');
            /* @var Query\Where $where */
            $where = $this->get('where');
            $source = $model->getSource();
            $table = $source['schema'][$alias];
            $from->from($table['table']);
            if (isset($table['id']) && $table['id']) {
                $where->field($table['id'])->is($model->get($table['id']));
            } else {
                $where->field($alias === 'primary'
                    ? $model->getPrimaryKey()
                    : 'primary_id')->is($model->id());
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function insertFromModel(IEntity $model, $alias = 'primary')
        {
            $this->insert();
            $this->setModel($model);
            /* @var Query\Insert $insert */
            $insert = $this->get('insert');
            /* @var Query\Where $select */
            /* @var Query\Bind $bind */
            $bind = $this->get('bind');
            $source = $model->getSource();
            $table = $source['schema'][$alias];
            $insert->table($table['table']);
            $data = $model->getChanged();
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $table['columns'])) {
                    $insert->add($key);
                    $bind->add($value);
                }
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function updateFromModel(IEntity $model, $alias = 'primary')
        {
            $this->update();
            $this->setModel($model);
            /* @var Query\Update $update */
            $update = $this->get('update');
            /* @var Query\Where $select */
            $where = $this->get('where');
            /* @var Query\Bind $bind */
            $bind = $this->get('bind');
            $source = $model->getSource();
            $table = $source['schema'][$alias];
            $update->table($table['table']);
            $data = $model->getChanged();
            $changed = false;
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $table['columns'])) {
                    $changed = true;
                    $update->add($key);
                    $bind->add($value);
                }
            }
            if ($changed) {
                if (isset($table['id']) && $table['id']) {
                    $where->field($table['id'])->is($model->get($table['id']));
                } else {
                    $where->field($alias === 'primary'
                        ? $model->getPrimaryKey()
                        : 'primary_id')->is($model->id());
                }
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function upsertFromModel(IEntity $model)
        {
            $this->setModel($model);
            /* @var Query\From $from */
            $from = $this->get('from');
            /* @var Query\Select $select */
            $select = $this->get('select');
            $source = $model->getSource();
            $id = $model->getPrimaryKey();
            foreach ($source['schema'] as $alias => $table) {
                if ($alias === 'primary') {
                    $from->from($table['table'], $alias);
                } else {
                    $from->leftJoin($table['table'], $alias, array_key_exists('mapping', $table)
                        ? $table['mapping']
                        : [
                            $id => array_key_exists('id', $table)
                                ? $table['id']
                                : 'id'
                        ]);
                }
                $select->field('*', $alias);
            }
            return $this;
        }

        /**
         * Inject our manager and model objects into elements when we first chose a type.
         */
        protected function injectManagerAndModel()
        {
            foreach ($this->elements as $element) {
                /* @var Query\Element $element */
                if ($this->hasManager()) {
                    $element->setManager($this->getManager());
                }
                if ($this->hasModel()) {
                    $element->setModel($this->getModel());
                }
            }
        }

    }
