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
     * Default core component calls.
     *
     * @package PHY\Component\AComponent
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    abstract class AComponent implements IComponent
    {

        protected $resources = [];
        protected $app;

        /**
         * {@inheritDoc}
         */
        public function __construct(App $app = null)
        {
            if ($app !== null) {
                $this->setApp($app);
            }
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
         * {@inheritDoc}
         */
        public function getName()
        {
            return trim(strtolower(str_replace(__NAMESPACE__, '', get_class($this))), '\\');
        }

        /**
         * Get our currently working namespace.
         *
         * @return string
         */
        public function getNamespace()
        {
            return $this
                ->getApp()
                ->getNamespace();
        }

        /**
         * {@inheritDoc}
         */
        public function get($key)
        {
            throw new Exception('This registry "'.get_called_class().'" component cannot use get.');
        }

        /**
         * {@inheritDoc}
         */
        public function delete($key)
        {
            throw new Exception('This registry "'.get_called_class().'" component cannot use delete.');
        }

        /**
         * {@inheritDoc}
         */
        public function set($key, $value)
        {
            throw new Exception('This registry "'.get_called_class().'" component cannot use set.');
        }

        /**
         * {@inheritDoc}
         */
        public function has($key)
        {
            throw new Exception('This registry "'.get_called_class().'" component cannot use has.');
        }

    }
