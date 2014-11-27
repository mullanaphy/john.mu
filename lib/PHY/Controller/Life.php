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

    /**
     * An implementation of Conway's Game of Life.
     *
     * @package PHY\Controller\Life
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Life extends \PHY\Controller\AController
    {

        /**
         * GET /life
         */
        public function index_get()
        {

        }

        /**
         * GET /life/glider
         */
        public function glider_get()
        {
            $content = $this->getLayout()->block('content');
            $content->setTemplate('life/glider.phtml');
        }

        /**
         * GET /life/pulsar
         */
        public function pulsar_get()
        {
            $content = $this->getLayout()->block('content');
            $content->setTemplate('life/pulsar.phtml');
        }

    }
