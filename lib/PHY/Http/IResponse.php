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

    namespace PHY\Http;

    /**
     * Response contract.
     *
     * @package PHY\Http\IResponse
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IResponse
    {

        /**
         * Inject in some Environmentals. Should probably have createResponseFromRequest or some rubbish like that.
         * Also, inject in some statusCodes, otherwise use PHP's defaults.
         *
         * @param array $environmentals
         * @param array $statusCodes
         */
        public function __construct(array $environmentals = [], $statusCodes = []);

        /**
         * See if our current response is a redirect.
         *
         * @return boolean
         */
        public function isRedirect();

        /**
         * Set a redirect instead of a page render.
         *
         * @param string $redirect
         * @param int $redirectStatus
         * @return IResponse
         */
        public function redirect($redirect, $redirectStatus = 301);

        /**
         * Render our headers and flush what we can.
         */
        public function renderHeaders();

        /**
         * See if we have headers to render.
         *
         * @return boolean
         */
        public function hasHeaders();

        /**
         * Get all the defined headers so far.
         *
         * @return array
         */
        public function getHeaders();

        /**
         * Set a single header.
         *
         * @param string $header
         * @param string $value
         * @return $this
         */
        public function setHeader($header, $value);

        /**
         * Get a single header if it exists.
         *
         * @param string $header
         * @return string|null
         */
        public function getHeader($header);

        /**
         * Return true if a given header already exists.
         *
         * @param string $header
         * @return boolean
         */
        public function hasHeader($header);

        /**
         * Render our response body.
         */
        public function renderContent();

        /**
         * See if our response has a body.
         *
         * @return boolean
         */
        public function hasContent();

        /**
         * Get our response body.
         *
         * @return array
         */
        public function getContent();

        /**
         * Add content to our response body.
         *
         * @param mixed $content
         * @return $this
         */
        public function addContent($content);

        /**
         * Set our response's content.
         *
         * @param array $content
         * @return $this
         */
        public function setContent(array $content);

        /**
         * Set a status code.
         *
         * @param int $code
         * @return $this
         */
        public function setStatusCode($code = 200);

        /**
         * Compress our response.
         *
         * @param boolean $compress
         * @return $this
         */
        public function setCompression($compress = true);
    }
