<?php

    /**
     * john.mu
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to hi@john.mu so we can send you a copy immediately.
     */

    namespace PHY\Model;

    use PHY\Encoder\IEncoder;
    use PHY\Encoder\PHPass;

    /**
     * The oh so generic user model.
     *
     * @package PHY\Model\User
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class User extends Entity implements IUser
    {

        protected $_initiated = null;
        protected $_password = '';
        protected static $_source = [
            'cacheable' => true,
            'schema' => [
                'primary' => [
                    'table' => 'user',
                    'columns' => [
                        'username' => 'variable',
                        'email' => 'variable',
                        'password' => 'variable',
                        'activity' => 'date',
                        'group' => ['regular', 'admin', 'super-admin'],
                        'title' => 'variable',
                        'name' => 'variable',
                        'bio' => 'text',
                        'phone' => 'variable',
                        'updated' => 'date',
                        'created' => 'date',
                        'deleted' => 'boolean'
                    ],
                    'keys' => [
                        'local' => [
                            'username' => 'unique',
                            'email' => 'unique'
                        ]
                    ]
                ]
            ]
        ];

        /**
         * {@inheritDoc}
         */
        public function checkPassword($password = '', $checkPassword = null)
        {
            if ($checkPassword === null) {
                if (!$this->exists()) {
                    throw new Exception('No password to check against. Please provide a second parameter or use an initiated User class.');
                } else {
                    $checkPassword = $this->data['password'];
                }
            }
            return (bool)$this->getEncoder()->checkPassword($password, $checkPassword);
        }

        /**
         * {@inheritDoc}
         */
        public function preSave()
        {
            if ($this->_password) {
                $this->data['password'] = $this->getEncoder()->hashPassword($this->_password);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function postSave($success)
        {
            if ($success) {
                $this->_password = null;
            }
        }

        /**
         * {@inheritDoc}
         */
        public function preLoad()
        {
            $this->_initiated = false;
        }

        /**
         * {@inheritDoc}
         */
        public function postLoad($success)
        {
            $this->_initiated = $success;
        }

        /**
         * {@inheritDoc}
         */
        public function get($key = '')
        {
            if ($key === 'password') {
                return null;
            } else {
                return parent::get($key);
            }
        }

        /**
         * Set a key to it's corresponding value if it's allowed
         *
         * @param string $key
         * @param mixed $value
         * @return $this
         */
        public function set($key = '', $value = '')
        {
            if ($key === 'password' && $this->_initiated !== false) {
                $this->_password = $value;
            } else {
                parent::set($key, $value);
            }
            return $this;
        }

        /**
         * Set our password encoder.
         *
         * @param IEncoder $encoder
         * @return $this
         */
        public function setEncoder(IEncoder $encoder)
        {
            $this->setResource('encoder', $encoder);
            return $this;
        }

        /**
         * Grab our password encoder.
         *
         * @return IEncoder
         */
        public function getEncoder()
        {
            if (!$this->hasResource('encoder')) {
                $this->setResource('encoder', new PHPass);
            }
            return $this->getResource('encoder');
        }

        /**
         * Get an array of all groups a user can view.
         *
         * @return array
         */
        public function getVisibility()
        {
            $visibility = [];
            switch ($this->get('group')) {
                case 'super-admin':
                    $visibility[] = 'super-admin';
                case 'admin':
                    $visibility[] = 'admin';
                case 'regular':
                    $visibility[] = 'regular';
                default:
                    $visibility[] = 'anonymous';
            }
            return $visibility;
        }

        /**
         * @return string
         */
        public function getThumbnail()
        {

        }

        /**
         * Get an image if one has been uploaded for this user.
         *
         * @param int|null $width
         * @param int|null $height
         * @return string
         */
        public function getImage($width = null, $height = null)
        {

        }
    }
