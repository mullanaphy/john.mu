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

    namespace PHY\Controller;

    use PHY\Model\User;

    /**
     * Default login controller.
     *
     * @package PHY\Controller\Login
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Login extends AController
    {

        /**
         * GET /login
         */
        public function index_get()
        {
            if ($this->getApp()->getUser()->exists()) {
                $this->redirect('/admin');
            } else {
                $this->getLayout()->block('layout')->setTemplate('core/layout-1col.phtml');
            }
        }

        /**
         * POST /login
         */
        public function index_post()
        {
            $request = $this->getRequest();
            $username = $request->get('username', false);
            $password = $request->get('password', false);
            $layout = $this->getLayout();
            $content = $layout->block('content');
            $content->setVariable('username', $username);
            if (!$username || !$password) {
                $this->index_get();
                $content->setChild('error', [
                    'template' => 'generic/message/error.phtml',
                    'message' => 'Please enter your Username and\or Password, then try again.',
                    'type' => 'error'
                ]);
            } else {
                $app = $this->getApp();
                /* @var \PHY\Database\IDatabase $database */
                $database = $app->get('database');
                $manager = $database->getManager();
                /* @var \PHY\Model\IUser $user */
                $user = $manager->load([
                    [
                        'username' => $username,
                        'email' => $username
                    ]
                ], new User);
                if ($user->exists() && $user->checkPassword($password)) {
                    $this->getApp()->set('session/user', $user->toArray());
                    $redirect = $app->get('session/_redirect');
                    $app->delete('session/_redirect');
                    if (!$redirect) {
                        $redirect = '/';
                    }
                    $this->redirect($redirect);
                } else {
                    $this->index_get();
                    $content->setChild('error', [
                        'template' => 'generic/message/error.phtml',
                        'message' => !$user->exists()
                            ? 'Username ' . $username . ' was not found. Please try again.'
                            : 'Wrong password...',
                        'type' => 'error'
                    ]);
                }
            }
        }

    }
