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
     * Handles all the response data.
     *
     * @package PHY\Http\Response
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Response implements IResponse
    {

        protected $headers = [];
        protected $content = [];
        protected $layout = null;
        protected $redirect = false;
        protected $redirectStatus = 301;
        protected $statusCode = 200;
        protected $statusCodes = [];
        protected $environmentals = [];
        protected $compress = false;
        protected static $_defaultHeaders = [];

        /**
         * {@inheritDoc}
         */
        public function __construct(array $environmentals = [], $statusCodes = [])
        {
            $this->environmentals = $environmentals;
            if ($statusCodes) {
                $this->statusCodes = $statusCodes;
            }
        }

        /**
         * {@inheritDoc}
         */
        public function isRedirect()
        {
            return (bool)$this->redirect;
        }

        /**
         * {@inheritDoc}
         */
        public function redirect($redirect, $redirectStatus = 301)
        {
            $this->redirect = $redirect;
            $this->redirectStatus = $redirectStatus;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function renderHeaders()
        {
            if ($this->isRedirect()) {
                header('Location: ' . $this->redirect, $this->redirectStatus);
            } else {
                if (array_key_exists($this->statusCode, $this->statusCodes)) {
                    $status = (array_key_exists('SERVER_PROTOCOL', $this->environmentals)
                            ? $this->environmentals['SERVER_PROTOCOL']
                            : 'HTTP/1.1') . ' ' . $this->statusCode . ' ' . $this->statusCodes[$this->statusCode];
                    header($status);
                } else {
                    http_response_code($this->statusCode);
                }
                if ($this->hasHeaders()) {
                    foreach ($this->getHeaders() as $key => $value) {
                        header($key . ': ' . $value);
                    }
                }
            }
            flush();
        }

        /**
         * {@inheritDoc}
         */
        public function hasHeaders()
        {
            return (bool)count($this->headers);
        }

        /**
         * {@inheritDoc}
         */
        public function getHeaders()
        {
            return $this->headers;
        }

        /**
         * {@inheritDoc}
         */
        public function setHeader($header, $value)
        {
            $this->headers[$header] = $value;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getHeader($header)
        {
            return array_key_exists($header, $this->headers)
                ? $this->headers[$header]
                : null;
        }

        /**
         * {@inheritDoc}
         */
        public function hasHeader($header)
        {
            return array_key_exists($header, $this->headers);
        }

        /**
         * {@inheritDoc}
         */
        public function renderContent()
        {
            if ($this->hasContent()) {
                if ($this->compress) {
                    ob_start('ob_gzhandler');
                }
                echo implode('', $this->getContent());
                if ($this->compress) {
                    ob_flush();
                }
            }
        }

        /**
         * {@inheritDoc}
         */
        public function hasContent()
        {
            return !$this->isRedirect() && count($this->content);
        }

        /**
         * {@inheritDoc}
         */
        public function getContent()
        {
            return $this->content;
        }

        /**
         * {@inheritDoc}
         */
        public function addContent($content)
        {
            $this->content[] = $content;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function setContent(array $content)
        {
            $this->content = $content;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function setStatusCode($code = 200)
        {
            $this->statusCode = $code;
            return $this;
        }

        public function setCompression($compress = true)
        {
            $this->compress = $compress;
            return $this;
        }

    }
