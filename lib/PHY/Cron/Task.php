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

    namespace PHY\Cron;

    /**
     * Tasks to run when called.
     *
     * @package PHY\Cron\Task
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Task
    {

        protected $settings = [
            'enabled' => false,
            'expr' => '',
            'label' => '',
            'controller' => 'null',
            'method' => '__construct',
            'parameters' => []
        ];

        /**
         * Set a task on load.
         *
         * @param array $task
         */
        public function __construct(array $task = [])
        {
            $this->set($task);
        }

        /**
         * Set a task value.
         *
         * @param string $key
         * @param string $value
         * @return $this
         * @throws Exception
         */
        public function set($key = null, $value = '')
        {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    if (array_key_exists($k, $this->settings)) {
                        $this->settings[$k] = $v;
                    }
                }
            } elseif (is_string($key) & array_key_exists($key, $this->settings)) {
                $this->settings[$key] = $value;
            } else {
                throw new Exception('Key `' . $key . '` does not exist in Cron\Task.');
            }
            return $this;
        }

        /**
         * Get a task value.
         *
         * @param string $key
         * @return string
         * @throws Exception
         */
        public function get($key = null)
        {
            if (array_key_exists($key, $this->settings)) {
                return $this->settings[$key];
            } else {
                throw new Exception('Key `' . $key . '` does not exist in Cron\Task.');
            }
        }

        /**
         * Get our response.
         *
         * @return array
         */
        public function response()
        {
            return $response;
        }

        /**
         * Run a task.
         *
         * @return array
         */
        public function run()
        {
            if (!$this->settings['enabled']) {
                $response = [
                    'status' => 403,
                    'response' => 'This task is currently disabled. #' . __LINE__
                ];
                return $response;
            }
            $Expr = new Expr($this->settings['expr']);
            if (!$Expr->check(time())) {
                $response = [
                    'status' => 500,
                    'response' => 'Task not scheduled to run at this time. #' . __LINE__
                ];
                return $response;
            }
            $response = call_user_func_array([
                str_replace('/', '\\', $this->settings['controller']),
                $this->settings['method']
            ], $this->settings['parameters']);
            switch (gettype($response)) {
                case 'bool':
                    $response = [
                        'status' => 200,
                        'response' => (int)$response
                    ];
                    break;
                case 'array':
                    if (!array_key_exists('status', $response) || $response['status'] < 200 || $response >= 600) {
                        $response = [
                            'status' => 200,
                            'response' => $response
                        ];
                    }
                    break;
                default:
                    $response = [
                        'status' => 200,
                        'response' => $response
                    ];
            }
            return $response;
        }

    }
