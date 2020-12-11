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

    namespace PHY\Database\Disk;

    use PHY\Cache\ICache;
    use PHY\Database\IDatabase;
    use PHY\Database\IManager;
    use PHY\Model\Collection;
    use PHY\Model\Exception;
    use PHY\Model\IEntity;
    use Symfony\Component\Yaml\Exception\ParseException;
    use Symfony\Component\Yaml\Yaml;

    /**
     * Manage models using Disk storage.
     *
     * @package PHY\Database\Disk
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Manager implements IManager
    {

        protected $cache = null;
        protected $database = null;
        protected $model = null;
        protected $root = null;

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
         * @return \PHY\Database\Disk
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
            self::createCollectionDirectory($modelEntity, $this->getDirectory());
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
         * @throws Exception
         */
        public function load($loadBy, IEntity $model)
        {
            $model->preLoad();
            $source = self::parseSource($model);

            $id = isset($loadBy[$source['id']]) && $loadBy[$source['id']]
                ? $loadBy[$source['id']]
                : false;

            if (!$id) {
                foreach ($source['schema'] as $alias => $table) {
                    foreach ($table['columns'] as $key => $type) {
                        if ('slug' === $type && isset($loadBy[$key]) && $loadBy[$key]) {
                            $id = $loadBy[$key];
                            break 2;
                        }
                    }
                }
            }

            if (!$id) {
                throw new Exception('Disk datastore must load via `id` or a field type of `slug`.');
            }

            $collectionDirectory = self::createCollectionDirectory($model, $this->getDirectory());
            $id = preg_replace('/[^a-zA-Z0-9_-]+/', '', $id);
            $entityDirectory = $collectionDirectory . $id . DIRECTORY_SEPARATOR;

            if (!is_dir($entityDirectory)) {
                return $model;
            }

            try {
                $data = [];
                foreach ($source['schema'] as $alias => $table) {
                    if (is_file($entityDirectory . $alias . '.yml')) {
                        $data += Yaml::parse(file_get_contents($entityDirectory . $alias . '.yml'));
                    }
                    foreach ($table['columns'] as $key => $type) {
                        if ('text' === $type && is_file($entityDirectory . $key . '.md')) {
                            $data[$key] = file_get_contents($entityDirectory . $key . '.md');
                        }
                    }
                }
                $data[$source['id']] = $id;
                $model->setInitialData($data);
            } catch (ParseException $e) {
                throw new Exception('Could not load YAML or Text for: `' . $id . '`');
            }

            $model->postLoad(true);
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

            $source = self::parseSource($model);
            $cacheable = $source['cacheable'] && $this->getCache() !== null;
            $success = true;
            try {
                $data = $model->toArray();

                $id = isset($data['slug'])
                    ? $data['slug']
                    : $model->id();
                foreach ($source['schema'] as $table) {
                    $tableDirectory = self::getTableDirectory($table, $this->getDirectory());
                    $entityDirectory = $tableDirectory . $id . DIRECTORY_SEPARATOR;
                    if (!is)
                }

                $collectionDirectory = self::createCollectionDirectory($model, $this->getDirectory());
                $id = isset($data['slug'])
                    ? $data['slug']
                    : $model->id();
                $entityDirectory = $collectionDirectory . $id . DIRECTORY_SEPARATOR;
                if (!is_dir($entityDirectory)) {
                    throw new \Exception('Entity `' . $id . '` does not exist.');
                }
                $data['updated'] = date('Y-m-d H:i:s');
                foreach ($source['schema'] as $alias => $table) {
                    $storeYaml = [];
                    foreach ($table['columns'] as $key => $type) {
                        if ('text' === $type) {
                            if (isset($data[$key])) {
                                file_put_contents($entityDirectory . $key . '.md', $data[$key]);
                            }
                        } else if (isset($data[$key])) {
                            $storeYaml[$key] = $data[$key];
                        }
                    }
                    file_put_contents($entityDirectory . $alias . '.yml', Yaml::dump($storeYaml));
                }
                $model->updated = $data['updated'];
            } catch (\Exception $exception) {
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
            if ($model->exists()) {
                return false;
            }
            $model->preInsert();

            $source = self::parseSource($model);
            $cacheable = $source['cacheable'] && $this->getCache() !== null;
            $success = true;

            try {
                $data = $model->toArray();
                $collectionDirectory = self::createCollectionDirectory($model, $this->getDirectory());
                if (isset($data['slug']) && $data['slug']) {
                    $id = $data['slug'];
                } else {
                    $id = uniqid();
                    if (isset($data['slug'])) {
                        $data['slug'] = $id;
                    }
                }
                $entityDirectory = $collectionDirectory . $id . DIRECTORY_SEPARATOR;
                if (is_dir($entityDirectory)) {
                    throw new \Exception('Entity `' . $id . '` already exists.');
                }
                $data['created'] = date('Y-m-d H:i:s');
                $data['updated'] = $data['created'];
                mkdir($entityDirectory);
                foreach ($source['schema'] as $alias => $table) {
                    $storeYaml = [];
                    foreach ($table['columns'] as $key => $type) {
                        if ('text' === $type) {
                            if (isset($data[$key])) {
                                file_put_contents($entityDirectory . $key . '.md', $data[$key]);
                            }
                        } else if (isset($data[$key])) {
                            $storeYaml[$key] = $data[$key];
                        }
                    }
                    file_put_contents($entityDirectory . $alias . '.yml', Yaml::dump($storeYaml));
                }
                $model->created = $data['created'];
                $model->updated = $data['created'];
            } catch (\Exception $exception) {
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
            if (!$model->exists()) {
                return false;
            }
            $source = self::parseSource($model);
            $model->preDelete();
            $success = true;
            foreach ($source['schema'] as $table) {
                $tableDirectory = self::getTableDirectory($table, $this->getDirectory());
                if (!is_dir($tableDirectory)) {
                    $success = false;
                    break;
                }
                $entityDirectory = $tableDirectory . $model->id() . DIRECTORY_SEPARATOR;
                if (!is_dir($entityDirectory)) {
                    $success = false;
                    break;
                }
                if (!rmdir($entityDirectory)) {
                    $success = false;
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
         * {@inheritDoc}
         */
        public function emptyTable(IEntity $model)
        {
            return $this->dropTable($model) && self::createTableDirectory($model, $this->getDirectory());
        }

        /**
         * {@inheritDoc}
         */
        public function dropTable(IEntity $model)
        {
            $source = self::parseSource($model);
            $ok = [];
            foreach ($source['schema'] as $table) {
                $tableDirectory = self::getTableDirectory($table, $this->getDirectory());
                $ok[] = is_dir($tableDirectory) && rmdir($tableDirectory);
            }
            foreach ($ok as $check) {
                if (!$check) {
                    return false;
                }
            }
            return true;
        }

        private static function getTableDirectory(array $table, $rootDirectory)
        {
            return $rootDirectory . DIRECTORY_SEPARATOR . $table['table'] . DIRECTORY_SEPARATOR;
        }

        private static function createTableDirectory(IEntity $model, $rootDirectory)
        {
            $source = self::parseSource($model);
            $ok = [];
            foreach ($source['schema'] as $table) {
                $tableDirectory = self::getTableDirectory($table, $rootDirectory);
                if (!is_dir($tableDirectory)) {
                    $ok[] = mkdir($tableDirectory);
                }
            }
            foreach ($ok as $check) {
                if (!$check) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Parse the source of our entity to a uniformed array for our various
         * Disk needs.
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
            return md5('disk/model/' . $name . '/' . $id);
        }

        /**
         * {@inheritDolc}
         */
        public function clean($string)
        {
            return $string;
        }

        /**
         * Get our working directory.
         *
         * @return string
         */
        public function getDirectory()
        {
            $directory = $this->getDatabase()->getDirectory();
            if ($directory) {
                return $directory;
            };
            return $this->getDatabase()->getRootDirectory();
        }

    }
