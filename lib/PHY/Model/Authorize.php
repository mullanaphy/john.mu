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

    use PHY\Database\IManager;

    /**
     * For ACL Authorization.
     *
     * @package PHY\Model\Authorize
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Authorize extends Entity
    {

        protected static $_source = [
            'cacheable' => true,
            'schema' => [
                'primary' => [
                    'table' => 'authorize',
                    'columns' => [
                        'request' => 'variable',
                        'allow' => [
                            'type' => 'variable',
                            'comment' => 'There is a space at the beginning and end only in Table view. It is for search reasons.'
                        ],
                        'deny' => [
                            'type' => 'variable',
                            'comment' => 'There is a space at the beginning and end only in Table view. It is for search reasons.'
                        ],
                        'updated' => 'date',
                        'created' => 'date',
                        'deleted' => 'boolean'
                    ]
                ]
            ]
        ];

        /**
         * See if a Request is allowed to be made.
         *
         * @param IUser $user
         * @return bool
         */
        public function isAllowed(IUser $user)
        {
            $allow = explode(' ', $this->data['allow']);
            $deny = explode(' ', $this->data['deny']);

            if ($user->exists()) {
                if (!$user->group) {
                    $user->group = 'all';
                }

                /* If it's root it has full access */
                if ($user->group === 'root') {
                    $allowed = true;
                } /* See if a user's ID is in the approved list and not in the denied list. */ elseif (in_array($user->id, $allow) && !in_array($user->id, $deny)) {
                    $allowed = true;
                } /* If not, see if he's in the denied list only. */ elseif (in_array($user->id, $deny)) {
                    $allowed = false;
                } /* If not, let's see if his group is in the allowed list and it's not in the denied list. */ elseif (in_array($user->group, $allow) && !in_array($user->group, $deny)) {
                    $allowed = true;
                } /* If not, let's see if his group is in the denied list. */ elseif (in_array($user->group, $deny)) {
                    $allowed = false;
                } /* If not, well let's see if everyone is allowed access and not in the denied list. */ elseif (in_array('all', $allow) && !in_array('all', $deny)) {
                    $allowed = true;
                } /* If not, we can see if everyone is denied. */ elseif (in_array('all', $deny)) {
                    $allowed = false;
                } /* Finally, if there isn't an explicit DENY all then they should have access. */ else {
                    $allowed = true;
                }
            } else {
                /* There's no logged in user, let's see if everyone is allowed access and not in the denied list. */
                if (in_array('all', $allow) && !in_array('all', $deny)) {
                    $allowed = true;
                } /* If not, we can see if everyone is denied. */ elseif (in_array('all', $deny)) {
                    $allowed = false;
                } /* Finally, if there isn't an explicit DENY all then they should have access. */ else {
                    $allowed = true;
                }
            }
            return $allowed;
        }

        /**
         * See if a user is denied to do set action.
         *
         * @param IUser $user
         * @return bool
         */
        public function isDenied(IUser $user)
        {
            return !$this->isAllowed($user);
        }

        /**
         * Add some spaces on the ends pre save.
         *
         * {@inheritDoc}
         */
        public function preSave()
        {
            $this->set('allow', ' ' . $this->get('allow') . ' ');
            $this->set('deny', ' ' . $this->get('deny') . ' ');
        }

        /**
         * Remove some spaces post save.
         *
         * {@inheritDoc}
         */
        public function postSave($success)
        {
            $this->set('allow', trim($this->get('allow')));
            $this->set('deny', trim($this->get('deny')));
        }

        /**
         * Remove some spaces post load.
         *
         * {@inheritDoc}
         */
        public function postLoad($success)
        {
            $this->postSave($success);
        }

        /**
         * Load a request by the path.
         *
         * @param string $request
         * @param IManager $manager
         * @return Authorize
         */
        public static function loadByRequest($request, IManager $manager)
        {
            return $manager->load(['request' => $request], new Authorize);
        }
    }
