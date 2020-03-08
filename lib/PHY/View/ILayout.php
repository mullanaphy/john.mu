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

    namespace PHY\View;

    use PHY\Controller\IController;

    /**
     * Contract for layouts.
     *
     * @package PHY\View\ILayout
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface ILayout
    {

        /**
         * Stringify our class.
         *
         * @return string
         */
        public function __toString();

        /**
         * Load config blocks to use with our layout.
         *
         * @return ILayout
         * @throws Layout\Exception
         */
        public function loadBlocks();

        /**
         * Return a block.
         *
         * @param string $block
         * @return IView
         */
        public function block($block);

        /**
         * Set our controller.
         *
         * @param IController $controller
         * @return ILayout
         */
        public function setController(IController $controller);

        /**
         * Get our working controller.
         *
         * @return IController
         */
        public function getController();

        /**
         * Get a stringified version of our layout.
         *
         * @return string
         */
        public function toString();

        /**
         * Render our layout.
         *
         * @return string
         */
        public function render();

        /**
         * Recursively build our blocks starting with 'layout'.
         *
         * @param string $key
         * @param array $config
         * @return ILayout
         * @throws Exception
         */
        public function buildBlocks($key, $config);

    }

