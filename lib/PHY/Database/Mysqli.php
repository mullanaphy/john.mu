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
    use PHY\Database\Mysqli\Manager;

    /**
     * Use Mysqli as your database of choice.
     *
     * @package PHY\Database\Mysqli
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Mysqli extends \Mysqli implements IDatabase
    {

        private $count = 0;
        private $multi = false;
        private $manager = null;
        private $app = null;

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
            parent::__construct(array_key_exists('host', $settings)
                ? $settings['host']
                : null, $settings['username'], $settings['password'], $settings['table'], array_key_exists('port', $settings)
                ? $settings['port']
                : null, array_key_exists('socket', $settings)
                ? $settings['socket']
                : null);
            $this->app = $app;
            if ($this->connect_error) {
                throw new Exception('Connection Error (' . $this->connect_errno . ') ' . $this->connect_error);
            }
        }

        /**
         * Prepare a SQL statement.
         *
         * @param string $sql
         * @return \Mysqli_STMT
         * @throws Exception
         */
        public function prepare($sql)
        {
            ++$this->count;
            $this->multi = false;
            $SQL = parent::prepare($sql);
            if ($this->error) {
                throw new Exception($this->error, $this->errno, $sql);
            } else {
                return $SQL;
            }
        }

        /**
         * Run a basic query.
         *
         * @param string $sql
         * @return \Mysqli_Result
         * @throws Exception
         */
        public function query($sql)
        {
            ++$this->count;
            $this->multi = false;
            $result = parent::query($sql);
            return $result;
        }

        /**
         * Run multiple queries.
         *
         * @param string $sql
         * @return \Mysqli_Result
         * @throws Exception
         */
        public function multi_query($sql)
        {
            ++$this->count;
            $this->multi = true;
            $SQL = parent::multi_query($sql);
            if ($this->error) {
                throw new Exception($this->error, $sql);
            } else {
                return $SQL;
            }
        }

        /**
         * DELETE statement.
         *
         * @param string $sql
         * @return int|bool Returns number of affected rows or false on failure.
         * @throws Exception
         */
        public function delete($sql)
        {
            ++$this->count;
            $this->multi = false;
            parent::query($sql);
            if ($this->error) {
                throw new Exception($this->error, $sql);
            } else {
                return $this->affected_rows;
            }
        }

        /**
         * INSERT statement.
         *
         * @param string $sql
         * @return int|bool Will return false on any error.
         * @throws Exception
         */
        public function insert($sql)
        {
            ++$this->count;
            $this->multi = false;
            parent::query($sql);
            if ($this->error) {
                throw new Exception($this->error, $sql);
            } else {
                return $this->insert_id;
            }
        }

        /**
         * SELECT statement.
         *
         * @param string $sql
         * @return \Mysqli_Result
         * @throws Exception
         */
        public function select($sql)
        {
            ++$this->count;
            $this->multi = false;
            $SQL = parent::query($sql);
            if ($this->error) {
                throw new Exception($this->error, $sql);
            } else {
                return $SQL;
            }
        }

        /**
         * UPDATE statement.
         *
         * @param string $sql
         * @return int|bool Returns number of affected rows or false on failure.
         * @throws Exception
         */
        public function update($sql)
        {
            ++$this->count;
            $this->multi = false;
            parent::query($sql);
            if ($this->error) {
                throw new Exception($this->error, $sql);
            } else {
                return $this->affected_rows;
            }
        }

        /**
         * Alias for real_escape_string.
         *
         * @param string $string
         * @return string
         */
        public function clean($string)
        {
            return $this->real_escape_string($string);
        }

        /**
         * Clear out all returned results after using a multi_query.
         */
        public function multi_free()
        {
            if ($this->multi) {
                while ($this->more_results()) {
                    $this->next_result();
                }
            }
            $this->multi = false;
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
            ++$this->count;
            $this->multi = false;
            $SQL = parent::query($sql);
            if ($this->error) {
                throw new Exception($this->error, $sql);
            }
            $result = $SQL->fetch_array();
            $SQL->close();
            return isset($result[0])
                ? $result[0]
                : false;
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
            ++$this->count;
            $this->multi = false;
            $SQL = parent::query($sql);
            if ($this->error) {
                throw new Exception($this->error, $sql);
            } elseif ($SQL->num_rows > 1) {
                throw new Exception('Your SQL returned ' . $SQL->num_rows . ' rows. Use select() and fetch_assoc() instead.', $sql);
            }
            $result = $SQL->fetch_assoc();
            $SQL->close();
            return $result;
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
    }
