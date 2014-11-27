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

    namespace PHY\Http\Response;

    use PHY\Http\Response;

    /**
     * Handles all the response data.
     *
     * @package PHY\Http\Response\Json
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Json extends Response
    {

        protected static $_defaultHeaders = [];

        /**
         * {@inheritDoc}
         */
        public function renderContent()
        {
            if ($this->hasContent()) {
                echo json_encode($this->getContent(), JSON_PRETTY_PRINT);
            }
        }

        /**
         * {@inheritDoc}
         */
        public function setData($data = [])
        {
            $this->headers['Content-Type'] = 'application/json';
            return $this->setContent($data);
        }

    }
