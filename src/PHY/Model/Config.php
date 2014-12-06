<?php

    /**
     * jo.mu
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to john@jo.mu so we can send you a copy immediately.
     */

    namespace PHY\Model;

    /**
     * For site configuration data when Jesse can edit stuff via an admin panel.
     *
     * @package PHY\Model\Config
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Config extends Entity
    {

        protected static $_source = [
            'cacheable' => true,
            'schema' => [
                'primary' => [
                    'table' => 'config',
                    'columns' => [
                        'key' => 'slug',
                        'type' => 'slug',
                    ],
                    'keys' => [
                        'local' => [
                            'key' => 'unique',
                        ]
                    ]
                ],
                'variable' => [
                    'id' => 'key_variable',
                    'table' => 'config_variable',
                    'columns' => [
                        'key_variable' => 'slug',
                        'value_variable' => 'variable'
                    ],
                    'keys' => [
                        'foreign' => [
                            'key_variable' => [
                                'table' => 'primary',
                                'key' => 'key',
                                'cascade' => true,
                            ]
                        ]
                    ],
                    'ignore' => ['key_variable'],
                ],
                'float' => [
                    'id' => 'key_float',
                    'table' => 'config_float',
                    'columns' => [
                        'key_float' => 'slug',
                        'value_float' => 'float'
                    ],
                    'keys' => [
                        'foreign' => [
                            'key_float' => [
                                'table' => 'primary',
                                'key' => 'key',
                                'cascade' => true,
                            ]
                        ]
                    ],
                    'ignore' => ['key_float'],
                ],
                'integer' => [
                    'id' => 'key_integer',
                    'table' => 'config_integer',
                    'columns' => [
                        'key_integer' => 'slug',
                        'value_integer' => 'int'
                    ],
                    'keys' => [
                        'foreign' => [
                            'key_integer' => [
                                'table' => 'primary',
                                'key' => 'key',
                                'cascade' => true,
                            ]
                        ]
                    ],
                    'ignore' => ['key_integer'],
                ],
                'boolean' => [
                    'id' => 'key_boolean',
                    'table' => 'config_boolean',
                    'columns' => [
                        'key_boolean' => 'slug',
                        'value_boolean' => 'boolean'
                    ],
                    'keys' => [
                        'foreign' => [
                            'key_boolean' => [
                                'table' => 'primary',
                                'key' => 'key',
                                'cascade' => true,
                            ]
                        ]
                    ],
                    'ignore' => ['key_boolean'],
                ],
                'date' => [
                    'id' => 'key_date',
                    'table' => 'config_date',
                    'columns' => [
                        'key_date' => 'slug',
                        'value_date' => 'date'
                    ],
                    'keys' => [
                        'foreign' => [
                            'key_date' => [
                                'table' => 'primary',
                                'key' => 'key',
                                'cascade' => true,
                            ]
                        ]
                    ],
                    'ignore' => ['key_date'],
                ],
                'decimal' => [
                    'id' => 'key_decimal',
                    'table' => 'config_decimal',
                    'columns' => [
                        'key_decimal' => 'slug',
                        'value_decimal' => 'decimal'
                    ],
                    'keys' => [
                        'foreign' => [
                            'key_decimal' => [
                                'table' => 'primary',
                                'key' => 'key',
                                'cascade' => true,
                            ]
                        ]
                    ],
                    'ignore' => ['key_decimal'],
                ],
                'text' => [
                    'id' => 'key_text',
                    'table' => 'config_text',
                    'columns' => [
                        'key_text' => 'slug',
                        'value_text' => 'text'
                    ],
                    'keys' => [
                        'foreign' => [
                            'key_text' => [
                                'table' => 'primary',
                                'key' => 'key',
                                'cascade' => true,
                            ]
                        ]
                    ],
                    'ignore' => ['key_text'],
                ]
            ]
        ];

        /**
         * {@inheritDoc}
         */
        public function get($key = '')
        {
            if ($key === 'value') {
                $type = $this->get('type');
                return array_key_exists('value_' . $type, $this->data)
                    ? $this->data['value_' . $type]
                    : null;
            } else {
                return parent::get($key);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function init(array $data = [])
        {
            if (array_key_exists('value', $data)) {
                if (array_key_exists('type', $data) && $data['type']) {
                    $data['value_' . $data['type']] = $data['value'];
                } else {
                    $data['type'] = 'variable';
                    $data['value_variable'] = $data['value'];
                }
                unset($data['value']);
            }
            return parent::init($data);
        }

        /**
         * {@inheritDoc}
         */
        public function set($key = '', $value = '')
        {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    $this->set($k, $v);
                }
            } else if ($key === 'value') {
                if (!array_key_exists('type', $this->data) || !$this->data['type']) {
                    $this->data['type'] = 'variable';
                }
                $this->data['key_' . $this->data['type']] = $this->get('key');
                $this->data['value_' . $this->data['type']] = $value;
            } else if ($key === 'type') {
                if (array_key_exists('type', $this->data) && $this->data['type']
                    && array_key_exists('key_' . $this->data['type'], $this->data)
                ) {
                    $val = $this->get('value');
                    unset($this->data['key_' . $this->data['type']], $this->data['value_' . $this->data['type']]);
                    $this->data['type'] = $value;
                    $this->set('value', $val);
                } else {
                    $this->data['type'] = $value;
                    $this->data['key_' . $value] = $this->get('key');
                }
            } else {
                return parent::set($key, $value);
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getChanged()
        {
            if (!$this->exists()) {
                if (!$type = $this->get('type')) {
                    $type = 'variable';
                }
                return [
                    'key' => $this->get('key'),
                    'type' => $type,
                    'key_' . $type => $this->get('key'),
                    'value_' . $type => $this->get('value')
                ];
            }
            if (!$this->isDifferent()) {
                return [];
            }
            $changed = [];
            $primary_key = $this->getPrimaryKey();
            foreach ($this->data as $key => $value) {
                if ($key === $primary_key) {
                    continue;
                } else if ($value !== $this->initial[$key]) {
                    $changed[$key] = $value;
                }
            }
            return $changed;
        }
    }