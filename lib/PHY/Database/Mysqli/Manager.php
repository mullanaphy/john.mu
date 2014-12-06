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

    use PHY\Database\ICollection;
    use PHY\Database\IManager;
    use PHY\Database\IDatabase;
    use PHY\Model\Collection;
    use PHY\Model\Exception;
    use PHY\Model\IEntity;
    use PHY\Cache\ICache;
    use PHY\Cache\Local as CacheLocal;
    use PHY\Variable\Int;

    /**
     * Manage models using Mysqli.
     *
     * @package PHY\Database\Mysqli\Database
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Manager implements IManager
    {

        protected $cache = null;
        protected $database = null;
        protected $model = null;
        private static $_tables = null;
        private static $_databases = [];
        private static $_fieldTypes = [
            'boolean' => 'tinyint(1) NOT NULL DEFAULT \'0\'',
            'date' => 'datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\'',
            'id' => 'int(16) unsigned NOT NULL',
            'int' => 'int(16) signed NOT NULL',
            'decimal' => 'decimal(8,4) signed NOT NULL',
            'float' => 'float(8,4) signed NOT NULL',
            'slug' => 'varchar(32) NOT NULL',
            'text' => 'text NOT NULL',
            'tinyint' => 'tinyint(4) signed NOT NULL',
            'variable' => 'varchar(255) NOT NULL'
        ];

        /**
         * {@inheritDoc}
         */
        public function __construct(IDatabase $database = null)
        {
            if ($database !== null) {
                $this->setDatabase($database);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function setDatabase(IDatabase $database)
        {
            $this->database = $database;
            return $this;
        }

        /**
         * {@inheritDoc}
         * @return \PHY\Database\Mysqli
         */
        public function getDatabase()
        {
            return $this->database;
        }

        /**
         * Set a cache to use with our manager.
         *
         * @param ICache $cache
         * @return $this
         */
        public function setCache(ICache $cache)
        {
            $this->cache = $cache;
            return $this;
        }

        /**
         * Return our defined cache model for leveraging our load.
         *
         * @return ICache
         */
        public function getCache()
        {
            if ($this->cache === null) {
                $this->cache = $this->getDatabase()->getApp()->get('cache');
            }
            return $this->cache;
        }

        /**
         * {@inheritDoc}
         */
        public function getModel($model)
        {
            $className = $this->getDatabase()
                ->getApp()
                ->getClass('Model\\' . implode('/', array_map('ucfirst', explode('\\', $model))));
            if (!$className) {
                throw new Exception('No usable model class found for ' . $model);
            }
            $model = new $className;
            if (!($model instanceof IEntity)) {
                throw new Exception($className . ' does not implement \PHY\Model\IEntity');
            }
            return $model;
        }

        /**
         * {@inheritDoc}
         */
        public function getCollection($model)
        {
            $app = $this->getDataBase()->getApp();
            $modelClassName = $app->getClass('Model\\' . $model);
            if (!$modelClassName) {
                throw new Exception('No class found for ' . $model);
            }
            $modelEntity = new $modelClassName;
            if (!($modelEntity instanceof IEntity)) {
                throw new Exception($modelClassName . ' does not implement \PHY\Model\IEntity');
            }
            self::createTable($modelEntity, $this->getDatabase(), $this->getCache());
            $collectionClassName = $app->getClass('Model\\' . implode('/', array_map('ucfirst', explode('\\', $model))) . '\Collection');
            if (!$collectionClassName) {
                throw new Exception('No collection class found for ' . $model);
            }
            /* @var $collection Collection */
            $collection = new $collectionClassName;
            if (!($collection instanceof Collection)) {
                throw new Exception($collectionClassName . ' does not extend \PHY\Model\Collection');
            }
            $collection->setManager($this);
            return $collection;
        }

        /**
         * {@inheritDoc}
         */
        public function load($loadBy, IEntity $model)
        {
            $model->preLoad();
            self::createTable($model, $this->getDatabase(), $this->getCache());
            $source = static::parseSource($model);
            $data = false;
            $cacheKey = false;
            $cache = $this->getCache();
            if ($cache !== null) {
                if ($source['cacheable']) {
                    if (is_scalar($loadBy)) {
                        $cacheKey = self::getCacheKey($source['name'], $loadBy);
                    } else {
                        if (count($loadBy) === 1) {
                            $key = key($loadBy);
                            if ($key === $source['id']) {
                                $cacheKey = self::getCacheKey($source['name'], $loadBy[$key]);
                            } else if (is_array($source['cacheable']) && in_array($key, $source['cacheable'])) {
                                $cacheKey = self::getCacheKey($source['name'], $loadBy[$key]);
                            }
                        } else {
                            ksort($loadBy);
                            if (in_array(array_keys($loadBy), $source['cacheable'])) {
                                $id = '';
                                foreach ($loadBy as $k => $v) {
                                    $id .= $k . '=' . $v;
                                }
                                $cacheKey = self::getCacheKey($source['name'], $id);
                            }
                        }
                    }
                }
            }
            if ($cacheKey) {
                $data = $cache->get($cacheKey);
            }
            if (!$data) {
                $query = $this->createQuery()->selectFromModel($model);
                if (!is_array($loadBy)) {
                    $loadBy = [$source['id'] => (int)$loadBy];
                }
                $where = $query->get('where');
                $columns = [];
                foreach ($source['schema'] as $alias => $table) {
                    foreach ($table['columns'] as $key => $value) {
                        $columns[$key] = $alias;
                    }
                }
                $columns[$source['id']] = 'primary';
                foreach ($loadBy as $key => $value) {
                    if (is_array($value)) {
                        if (isset($columns[$key])) {
                            $where->field($key, $columns[$key])->in($value);
                        } else {
                            $start = 0;
                            foreach ($value as $k => $v) {
                                if (isset($columns[$k])) {
                                    if (!$start++) {
                                        if (is_array($v)) {
                                            $where->field($k, $columns[$k])->in($v);
                                        } else {
                                            $where->field($k, $columns[$k])->is($v);
                                        }
                                    } else {
                                        if (is_array($v)) {
                                            $where->instead($k, $columns[$k])->in($v);
                                        } else {
                                            $where->instead($k, $columns[$k])->is($v);
                                        }
                                    }
                                }
                            }
                        }
                    } else if (isset($columns[$key])) {
                        $where->field($key, $columns[$key])->is($value);
                    }
                }
                $query->execute();
                $data = $query->getIterator()->fetch_assoc();
                if ($data) {
                    foreach ($data as $key => $value) {
                        if ($key === $source['id']) {
                            $column = 'id';
                        } else {
                            $column = isset($columns[$key], $source[$columns[$key]][$key])
                                ? $source[$columns[$key]][$key]
                                : 'variable';
                            if (is_array($column)) {
                                $column = $column['type'];
                            }
                        }
                        switch ($column) {
                            case 'boolean':
                                $data[$key] = (bool)$data[$key];
                                break;
                            case 'id':
                            case 'int':
                            case 'tinyint':
                                $data[$key] = (int)$data[$key];
                                break;
                            case 'decimal':
                            case 'float':
                                $data[$key] = (double)$data[$key];
                                break;
                            case 'variable':
                            default:
                                $data[$key] = (string)$data[$key];
                                break;
                        }
                    }
                }
            }
            if ($data) {
                $success = true;
                $model->setInitialData($data);
                if ($cacheKey) {
                    $cache->set(self::getCacheKey($source['name'], $model->id()), $model->toArray());
                    if (is_array($source['cacheable'])) {
                        foreach ($source['cacheable'] as $key) {
                            if (is_array($key)) {
                                $id = '';
                                foreach ($key as $k) {
                                    $id .= $k . '=' . $model->get($k);
                                }
                                $cacheKey = self::getCacheKey($source['name'], $id);
                            } else {
                                $cacheKey = self::getCacheKey($source['name'], $model->get($key));
                            }
                            $cache->set($cacheKey, $model->toArray());
                        }
                    }
                }
            } else {
                $success = false;
            }
            $model->postLoad($success);
            return $model;
        }

        /**
         * {@inheritDoc}
         */
        public function save(IEntity $model)
        {
            $model->preSave();
            if ($model->exists()) {
                $success = $this->update($model);
            } else {
                $success = $this->insert($model);
            }
            $model->postSave($success);
            return $success;
        }

        /**
         * {@inheritDoc}
         */
        public function update(IEntity $model)
        {
            $model->preLoad();
            if (!$model->isDifferent()) {
                return false;
            }
            $db = $this->getDatabase();
            self::createTable($model, $db, $this->getCache());
            $source = self::parseSource($model);
            $cacheable = $source['cacheable'] && $this->getCache() !== null;
            $success = true;
            try {
                $db->autocommit(false);
                foreach ($source['schema'] as $alias => $table) {
                    $query = $this->createQuery()->updateFromModel($model, $alias);
                    if ($query) {
                        if (!$query->execute()) {
                            throw new \Exception('Abra Cadabra.');
                        }
                    }
                }
                $db->commit();
                $db->autocommit(true);
            } catch (\Exception $exception) {
                $db->rollback();
                $db->autocommit(true);
                $success = false;
            }
            if ($success && $cacheable) {
                $cache = $this->getCache();
                $cacheKey = self::getCacheKey($source['name'], $model->id());
                $cache->replace($cacheKey, $model->toArray());
                if (is_array($source['cacheable'])) {
                    foreach ($source['cacheable'] as $key) {
                        if (is_array($key)) {
                            $id = '';
                            foreach ($key as $k) {
                                $id .= $k . '=' . $model->get($k);
                            }
                            $cacheKey = self::getCacheKey($source['name'], $id);
                        } else {
                            $cacheKey = self::getCacheKey($source['name'], $model->get($key));
                        }
                        $cache->replace($cacheKey, $model->toArray());
                    }
                }
            }
            $model->postUpdate($success);
            return $success;
        }

        /**
         * {@inheritDoc}
         */
        public function insert(IEntity $model)
        {
            $model->preInsert();
            if ($model->exists()) {
                return false;
            }
            $db = $this->getDatabase();
            self::createTable($model, $db, $this->getCache());
            $source = self::parseSource($model);
            $cacheable = $source['cacheable'] && $this->getCache() !== null;
            $success = true;
            try {
                $db->autocommit(false);
                foreach ($source['schema'] as $alias => $table) {
                    $query = $this->createQuery()->insertFromModel($model, $alias);
                    if ($query) {
                        if (!$query->execute()) {
                            throw new \Exception('Abra Cadabra.');
                        }
                    }
                }
                $db->commit();
                $db->autocommit(true);
            } catch (\Exception $exception) {
                $db->rollback();
                $db->autocommit(true);
                $success = false;
            }
            if ($success && $cacheable) {
                $cache = $this->getCache();
                $cacheKey = self::getCacheKey($source['name'], $model->id());
                $cache->delete($cacheKey);
                if (is_array($source['cacheable'])) {
                    foreach ($source['cacheable'] as $key) {
                        if (is_array($key)) {
                            $id = '';
                            foreach ($key as $k) {
                                $id .= $k . '=' . $model->get($k);
                            }
                            $cacheKey = self::getCacheKey($source['name'], $id);
                        } else {
                            $cacheKey = self::getCacheKey($source['name'], $model->get($key));
                        }
                        $cache->set($cacheKey, $model->toArray());
                    }
                }
            }
            $model->postInsert($success);
            return $success;
        }

        /**
         * {@inheritDoc}
         */
        public function delete(IEntity $model)
        {
            $model->preDelete();
            if (!$model->exists()) {
                return false;
            }
            $db = $this->getDatabase();
            self::createTable($model, $db, $this->getCache());
            $source = self::parseSource($model);
            $cacheable = $source['cacheable'] && $this->getCache() !== null;
            $success = true;
            try {
                $db->autocommit(false);
                foreach ($source['schema'] as $alias => $table) {
                    $query = $this->createQuery()->deleteFromModel($model, $alias);
                    if ($query) {
                        if (!$query->execute()) {
                            throw new \Exception('Abra Cadabra.');
                        }
                    }
                }
                $db->commit();
                $db->autocommit(true);
            } catch (\Exception $exception) {
                $db->rollback();
                $db->autocommit(true);
                $success = false;
            }
            if ($success && $cacheable) {
                $cache = $this->getCache();
                $cacheKey = self::getCacheKey($source['name'], $model->id());
                $cache->delete($cacheKey);
                if (is_array($source['cacheable'])) {
                    foreach ($source['cacheable'] as $key) {
                        if (is_array($key)) {
                            $id = '';
                            foreach ($key as $k) {
                                $id .= $k . '=' . $model->get($k);
                            }
                            $cacheKey = self::getCacheKey($source['name'], $id);
                        } else {
                            $cacheKey = self::getCacheKey($source['name'], $model->get($key));
                        }
                        $cache->delete($cacheKey);
                    }
                }
            }
            $model->postDelete($success);
            return $success;
        }

        /**
         * {@inheritDoc}
         */
        public function createQuery()
        {
            return new Query($this);
        }

        /**
         * Create a given table if it doesn't already exist and cache the
         * information pertaining to it so we don't need to keep checking during
         * every run through of our app.
         *
         * @param IEntity $model
         * @param IDatabase $database
         * @param ICache $cache
         * @return boolean
         * @throws \Exception
         */
        private static function createTable(IEntity $model, IDatabase $database, ICache $cache)
        {
            if (self::$_tables === null) {
                self::$_tables = $cache->get('mysqli/tables');
                if (!is_array(self::$_tables)) {
                    self::$_tables = [];
                }
            }
            $changed = false;
            $database->autocommit(false);
            $source = self::parseSource($model);
            foreach ($source['schema'] as $alias => $table) {
                if (!array_key_exists($table['table'], self::$_tables) || !self::$_tables[$table['table']]) {
                    $databaseName = static::getDatabaseName($database);
                    $check = $database->single("SHOW TABLES WHERE Tables_in_" . $databaseName . " = '" . $table['table'] . "'");
                    if (!$check) {
                        try {
                            $id = array_key_exists('id', $table)
                                ? $table['id']
                                : 'id';
                            $charset = array_key_exists('charset', $table)
                                ? $table['charset']
                                : 'utf8';
                            $engine = array_key_exists('engine', $table)
                                ? $table['engine']
                                : 'InnoDB';
                            if ($id) {
                                $fields = [$id => '`' . $id . '` int(16) unsigned NOT NULL AUTO_INCREMENT'];
                            } else {
                                $fields = [];
                            }
                            $keys = array_key_exists('keys', $table)
                                ? $table['keys']
                                : ['local' => [], 'foreign' => []];
                            if (!array_key_exists('local', $keys)) {
                                $keys['local'] = [];
                            }
                            if (!array_key_exists('foreign', $keys)) {
                                $keys['foreign'] = [];
                            }
                            if ($id && !array_key_exists($id, $keys['local'])) {
                                $keys['local'][$id] = 'PRIMARY KEY (`' . $id . '`)';
                            }
                            foreach ($keys['local'] as $key => $index) {
                                switch ($index) {
                                    case 'unique':
                                        $keys['local'][$key] = 'UNIQUE INDEX (`' . $key . '`)';
                                        break;
                                    case 'index':
                                        $keys['local'][$key] = 'INDEX (`' . $key . '`)';
                                        break;
                                    case 'fulltext':
                                        $keys['local'][$key] = 'FULLTEXT(`' . $key . '`)';
                                        break;
                                    case 'spatial':
                                        $keys['local'][$key] = 'SPATIAL INDEX(`' . $key . '`)';
                                        break;
                                }
                            }
                            foreach ($keys['foreign'] as $key => $index) {
                                if (is_array($index)) {
                                    $keys['local'][$key] = "UNIQUE INDEX (`" . $key . "`)";
                                    $keys['foreign'][$key] = "CONSTRAINT `" . $table['table'] . "_" . $index['key'] . "_" . $key . "` FOREIGN KEY (`" . $key . "`) ";
                                    $keys['foreign'][$key] .= "REFERENCES `" . $databaseName . "`.`" . $source['schema'][$index['table']]['table'] . "` (`" . $index['key'] . "`) ";
                                    if (array_key_exists('cascade', $index)) {
                                        if (is_array($index['cascade'])) {
                                            if (array_key_exists('update', $index['cascade']) && $index['cascade']['update']) {
                                                $keys['foreign'][$key] .= " ON UPDATE CASCADE ";
                                            }
                                            if (array_key_exists('delete', $index['cascade']) && $index['cascade']['delete']) {
                                                $keys['foreign'][$key] .= " ON DELETE CASCADE ";
                                            }
                                        } else if ($index['cascade']) {
                                            $keys['foreign'][$key] .= " ON UPDATE CASCADE ON DELETE CASCADE";
                                        }
                                    }
                                }
                            }
                            foreach ($table['columns'] as $key => $type) {
                                if (is_array($type) && array_key_exists('type', $type)) {
                                    $fields[$key] = '`' . $key . '` ' . self::getFieldType($type['type']);
                                    if (array_key_exists('comment', $type)) {
                                        $fields[$key] .= ' COMMENT \'' . $type['comment'] . ' \'';
                                    }
                                } else {
                                    $fields[$key] = '`' . $key . '` ' . self::getFieldType($type);
                                }
                            }
                            $database->query("CREATE TABLE IF NOT EXISTS `" . $databaseName . "`.`" . $table['table'] . "` (" . implode(",", $fields) . "," . implode(",", $keys['local']) . "
                                " . ($keys['foreign']
                                    ? "," . implode(",", $keys['foreign'])
                                    : "") . "
                                ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . ";");
                            if ($database->error) {
                                throw new Exception($database->error);
                            }
                            self::$_tables[$table['table']] = true;
                            if (array_key_exists('filler', $table) && $table['filler']) {
                                /* @var IEntity $item */
                                $item = new $model();
                                foreach ($table['filler'] as $key => $value) {
                                    $item->set($key, $value);
                                }
                                $database->getManager()->save($item);
                            }
                        } catch (\Exception $e) {
                            self::$_tables[$table['table']] = false;
                            $database->rollback();
                            $database->autocommit(true);
                            throw $e;
                        };
                    } else {
                        self::$_tables[$table['table']] = true;
                    }
                    $changed = true;
                }
            }
            $database->commit();
            $database->autocommit(true);
            if ($changed) {
                $cache->delete('mysqli/tables');
                $cache->set('mysqli/tables', self::$_tables, Int::YEAR);
            }

            return true;
        }

        /**
         * {@inheritDoc}
         */
        public function emptyTable(IEntity $model)
        {
            $database = $this->getDatabase();
            $databaseName = self::getDatabaseName($database);
            $source = $model->getSource();
            $primary = $source['schema']['primary'];
            unset($source['schema']['primary']);
            try {
                if ($source['schema']) {
                    foreach ($source['schema'] as $table) {
                        $database->query("TRUNCATE TABLE IF EXISTS `" . $databaseName . "`.`" . $table['table'] . ";");
                        self::$_tables[$table['table']] = false;
                    }
                }
                $database->query("TRUNCATE TABLE IF EXISTS `" . $databaseName . "`.`" . $primary['table'] . ";");
                $database->commit();
                $database->autocommit(true);
                $success = true;
            } catch (\Exception $e) {
                $database->rollback();
                $database->autocommit(true);
                $success = false;
            }
            return $success;
        }

        /**
         * {@inheritDoc}
         */
        public function dropTable(IEntity $model)
        {
            $database = $this->getDatabase();
            $databaseName = self::getDatabaseName($database);
            $source = $model->getSource();
            $primary = $source['schema']['primary'];
            unset($source['schema']['primary']);
            try {
                if ($source['schema']) {
                    foreach ($source['schema'] as $table) {
                        $database->query("DROP TABLE IF EXISTS `" . $databaseName . "`.`" . $table['table'] . ";");
                        unset(self::$_tables[$table['table']]);
                    }
                }
                $database->query("DROP TABLE IF EXISTS `" . $databaseName . "`.`" . $primary['table'] . ";");
                unset(self::$_tables[$primary['table']]);
                $database->commit();
                $database->autocommit(true);
                $success = true;
            } catch (\Exception $e) {
                $database->rollback();
                $database->autocommit(true);
                $success = false;
            }
            $cache = $this->getCache();
            $cache->delete('mysqli/tables');
            $cache->set('mysqli/tables', self::$_tables, Int::YEAR);
            return $success;
        }

        /**
         * Parse the source of our entity to a uniformed array for our various
         * Mysqli needs.
         *
         * @param IEntity $model
         * @return array
         */
        private static function parseSource(IEntity $model)
        {
            $source = $model->getSource();
            if (!array_key_exists('cacheable', $source)) {
                $source['cacheable'] = false;
            }
            if (!array_key_exists('id', $source)) {
                if (array_key_exists('id', $source['schema']['primary'])) {
                    $source['id'] = $source['schema']['primary']['id'];
                } else {
                    $source['id'] = 'id';
                }
            }
            $source['name'] = get_class($model);
            return $source;
        }

        /**
         * Generate a unified cache key.
         *
         * @param string $name
         * @param mixed $id
         * @return string
         */
        private static function getCacheKey($name, $id)
        {
            return md5('mysqli/model/' . $name . '/' . $id);
        }

        /**
         * Grab our database's name
         *
         * @param IDatabase $database
         * @return string
         */
        private static function getDatabaseName(IDatabase $database)
        {
            $table = md5($database->host_info);
            if (!array_key_exists($table, static::$_databases)) {
                static::$_databases[$table] = $database->single("SELECT DATABASE()");
            }
            return array_key_exists($table, static::$_databases)
                ? static::$_databases[$table]
                : '';
        }

        /**
         * Get a specific table's data for saving/retrieving in our database.
         *
         * @param array $table
         * @param array $data
         * @return array
         */
        private static function getTableData(array $table, array $data)
        {
            $row = [];
            foreach ($table as $key => $value) {
                if (array_key_exists($key, $data)) {
                    $row[$key] = $data[$key];
                }
            }
            return $row;
        }

        /**
         *
         * @param mixed $type
         * @return string $type
         */
        private static function getFieldType($type)
        {
            if (is_array($type)) {
                return "ENUM('" . join("','", $type) . "') NOT NULL";
            } else {
                return array_key_exists($type, static::$_fieldTypes)
                    ? static::$_fieldTypes[$type]
                    : $type;
            }
        }

        /**
         * {@inheritDolc}
         */
        public function clean($string)
        {
            return $this->getDatabase()->clean($string);
        }

    }
