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

    namespace PHY\Database;

    use PHY\App;
    use PHY\Database\Disk\Manager;

    /**
     * Use Disk as your datastore of choice.
     *
     * @package PHY\Database\Disk
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Disk implements IDatabase
    {

        private $manager = null;
        private $app = null;
        private $root = null;
        private $directory = null;

        /**
         * Extend this just so we can throw out an error if our Database is
         * acting flaky.
         *
         * @param array $settings
         * @param App $app
         * @throws Exception
         */
        public function __construct(array $settings = [], App $app = null)
        {
            $this->app = $app;
            $this->root = isset($settings['root'])
                ? $settings['root']
                : $app->getRootDirectory();
            $this->directory = isset($settings['directory'])
                ? implode(DIRECTORY_SEPARATOR, [$this->root, $settings['directory']])
                : $this->root;
            if (!is_dir($this->root)) {
                throw new Exception('No root directory (' . $this->root . ')');
            }
            if (!is_dir($this->directory)) {
                throw new Exception('No base directory (' . $this->directory . ')');
            }
        }

        /**
         * Prepare a SQL statement.
         *
         * @param string $sql
         * @throws Exception
         */
        public function prepare($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * Run a basic query.
         *
         * @param string $sql
         * @throws Exception
         */
        public function query($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * Run multiple queries.
         *
         * @param string $sql
         * @throws Exception
         */
        public function multi_query($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * DELETE statement.
         *
         * @param string $sql
         * @throws Exception
         */
        public function delete($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * INSERT statement.
         *
         * @param string $sql
         * @throws Exception
         */
        public function insert($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * SELECT statement.
         *
         * @param string $sql
         * @throws Exception
         */
        public function select($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * UPDATE statement.
         *
         * @param string $sql
         * @throws Exception
         */
        public function update($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * No cleaning necessary for data.
         *
         * @param string $string
         * @return string
         */
        public function clean($string)
        {
            return $string;
        }

        /**
         * Clear out all returned results after using a multi_query.
         */
        public function multi_free()
        {
        }

        /**
         * Return a single value from the database.
         *
         * @param string $sql
         * @return mixed
         * @throws Exception
         */
        public function single($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * Return a single row from a SELECT statement.
         *
         * @param string $sql
         * @return array
         * @throws Exception
         */
        public function row($sql)
        {
            throw new Exception('No implementation necessary for Disk storage.');
        }

        /**
         * {@inheritDoc}
         */
        public function setManager(IManager $manager)
        {
            $this->manager = $manager;
            $this->manager->setDatabase($this);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getManager($entity = '')
        {
            if ($this->manager === null) {
                $this->setManager(new Manager);
            }
            if ($entity) {
                $model = $this->getApp()->getClass('Model\\' . ucfirst($entity));
                if ($model) {
                    $this->manager->load(new $model);
                }
            }
            return $this->manager;
        }

        /**
         * {@inheritDoc}
         */
        public function setApp(App $app)
        {
            $this->app = $app;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getApp()
        {
            return $this->app;
        }

        /**
         * Get the root directory for our collections. Can potentially be different than `getDirectory`.
         *
         * @return mixed|string|null
         */
        public function getRootDirectory()
        {
            return $this->root;
        }

        /**
         * Get the base directory for our collections.
         *
         * @return string
         */
        public function getDirectory()
        {
            return $this->directory;
        }
    }
