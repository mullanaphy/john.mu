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

    use PHY\Http\IRequest;
    use PHY\Http\IResponse;
    use PHY\View\ILayout;
    use PHY\App;

    /**
     * Interface for Controllers.
     *
     * @package PHY\Controller\IController
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    interface IController
    {

        /**
         * Run our requested action from /{:controller}/{:action}
         *
         * @param string $method
         */
        public function action($method = 'index');

        /**
         * Grab our current request.
         *
         * @return IRequest
         */
        public function getRequest();

        /**
         * Set our current request.
         *
         * @param IRequest $request
         * @return IController
         */
        public function setRequest(IRequest $request);

        /**
         * Grab our current response.
         *
         * @return IResponse
         */
        public function getResponse();

        /**
         * Set our current response.
         *
         * @param IResponse $response
         * @return IController
         */
        public function setResponse(IResponse $response);

        /**
         * Grab our current layout.
         *
         * @return ILayout
         */
        public function getLayout();

        /**
         * Set our current layout.
         *
         * @param ILayout $layout
         * @return IController
         */
        public function setLayout(ILayout $layout);

        /**
         * Inject our app.
         *
         * @param App $app
         * @return IController
         */
        public function setApp(App $app);

        /**
         * Get our app.
         *
         * @return App
         */
        public function getApp();

        /**
         * GET /{:controller}/index
         */
        public function index_get();

        /**
         * Render a controller.
         *
         * @return IResponse
         */
        public function render();
    }
