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

    use PHY\App;
    use PHY\Http\Exception as HttpException;

    /**
     * Home page.
     *
     * @package PHY\Controller\Index
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Error extends AController
    {

        protected $message = 'Sorry, seems like some stuff broke... Please don\'t judge me harshly...';
        protected $statusCode = 500;
        protected $exception = null;

        /**
         * {@inheritDoc}
         */
        public function __construct(App $app = null)
        {
            parent::__construct($app);
            $layout = $this->getLayout();
            $layout->loadBlocks('default', 'error');
        }

        /**
         * We're overwriting the main action since we don't want to do any
         * component calls that aren't needed. In case there's something like a
         * missing database error, config error, or other.
         *
         * @param string $action
         * @return null
         */
        public function action($action = 'index')
        {
            /* Grab our request. */
            $request = $this->getRequest();

            /* See which route we should go with, depending on whether those methods exist or not. */
            $actions = [
                $action . '_' . $request->getMethod(),
                $action . '_get',
                'index_' . $request->getMethod()
            ];
            $action = 'index_get';
            foreach ($actions as $check) {
                if (method_exists($this, $check)) {
                    $action = $check;
                    break;
                }
            }

            $this->$action();
        }

        /**
         * Set our error message.
         *
         * @param string $message
         * @return $this
         */
        public function setMessage($message = '')
        {
            $this->message = $message;
            return $this;
        }

        /**
         * Get our error message.
         *
         * @return string
         */
        public function getMessage()
        {
            return $this->message;
        }

        /**
         * Set our exception.
         *
         * @param \Exception $exception
         * @return $this
         */
        public function setException(\Exception $exception)
        {
            $this->exception = $exception;
            return $this;
        }

        /**
         * Get our exception.
         *
         * @return \Exception
         */
        public function getException()
        {
            return $this->exception;
        }

        /**
         * Set our status code.
         *
         * @param int $statusCode
         * @return $this
         */
        public function setStatusCode($statusCode = 500)
        {
            $this->statusCode = $statusCode;
            return $this;
        }

        /**
         * Get our status code.
         *
         * @return int
         */
        public function getStatusCode()
        {
            return $this->statusCode;
        }

        /**
         * Report a HTTP exception.
         *
         * @param HttpException $exception
         * @return $this
         */
        public function httpException(HttpException $exception)
        {
            $this->setMessage($exception->getMessage());
            $this->setStatusCode($exception->getStatusCode());
            $this->setException($exception);
            return $this;
        }

        /**
         * GET /error
         */
        public function index_get()
        {
            $this->getResponse()->setStatusCode($this->getStatusCode());
            $layout = $this->getLayout();
            $layout->block('layout')->setTemplate('core/layout-error.phtml');
            $layout->block('content')->setVariable('title', 'Sour Hour!');
            if (!is_file($this->getApp()->getRootDirectory() . DIRECTORY_SEPARATOR . 'hideExceptions')) {
                $layout->block('error/exception')->setVariable('exception', $this->getException());
            }
            $layout->block('error/message')->setVariable('message', $this->getMessage());
            $this->getResponse()->addContent($this->getLayout());
        }

    }
