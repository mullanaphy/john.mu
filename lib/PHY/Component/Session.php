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

    namespace PHY\Component;

    use PHY\App;

    /**
     * Global Session class.
     *
     * @package PHY\Component\Session
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Session extends AComponent
    {

        /**
         * {@inheritDoc}
         */
        public function __construct(App $app = null)
        {
            switch (session_status()) {
                case PHP_SESSION_DISABLED:
                    throw new Exception('Sessions are disabled so use component cookie/ instead');
                    break;
                case PHP_SESSION_NONE:
                    session_start();
                    break;
            }
            if (!array_key_exists('PHY', $_SESSION)) {
                $_SESSION['PHY'] = [];
            }
            parent::__construct($app);
        }

        /**
         * {@inheritDoc}
         */
        public function delete($key)
        {
            if (array_key_exists($key, $_SESSION['PHY'])) {
                unset($_SESSION['PHY'][$key]);
                return true;
            }
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function get($key)
        {
            return array_key_exists($key, $_SESSION['PHY'])
                ? $_SESSION['PHY'][$key]
                : null;
        }

        /**
         * {@inheritDoc}
         */
        public function has($key)
        {
            return array_key_exists($key, $_SESSION['PHY']);
        }

        /**
         * {@inheritDoc}
         */
        public function set($key, $value)
        {
            if (!is_string($key)) {
                throw new Exception('A session key must be a string.');
            }
            $_SESSION['PHY'][$key] = $value;
            return true;
        }

    }
