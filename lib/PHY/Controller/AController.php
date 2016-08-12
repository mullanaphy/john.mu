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
    use PHY\Event;
    use PHY\Event\Item as EventItem;
    use PHY\Http\Exception\Forbidden;
    use PHY\Http\Exception\NotFound as HttpNotFoundException;
    use PHY\Http\IRequest;
    use PHY\Http\IResponse;
    use PHY\Http\Request;
    use PHY\Http\Response;
    use PHY\View\ILayout;
    use PHY\View\Layout;
    use PHY\Model\Authorize;

    /**
     * Boilerplate abstract class for Controllers.
     *
     * @package PHY\Controller\AController
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    abstract class AController implements IController
    {

        protected $app = null;
        protected $config = null;
        protected $request = null;
        protected $redirect = null;
        protected $response = null;
        protected $layout = null;
        protected $parsed = false;
        protected static $_design = [];
        protected static $_theme = 'default';

        /**
         * Inject our app into our controller.
         *
         * @param App $app
         */
        public function __construct(App $app = null)
        {
            if ($app !== null) {
                $this->setApp($app);
            }
        }

        /**
         * Render our controller if we haven't already.
         */
        public function __destruct()
        {
            if (!$this->parsed) {
                $this->render();
            }
        }

        /**
         * {@inheritDoc}
         */
        public function index_get()
        {
            throw new HttpNotFoundException('No routes were found for this call... Sorry about that.');
        }

        /**
         * {@inheritDoc}
         */
        public function action($action = 'index')
        {
            $app = $this->getApp();

            $event = new EventItem('controller/action/before', [
                'controller' => $this,
                'action' => $action
            ]);
            Event::dispatch($event);
            $action = $event->action;
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

            /* Check our ACL table to see if this user can view the action/method or not. */
            $check = trim(strtolower(str_replace([__NAMESPACE__, '\\'], ['', '/'], get_class($this))), '/');

            /* @var \PHY\Database\IManager $manager */
            /*$manager = $app->get('database')->getManager();
            $authorize = Authorize::loadByRequest($check . '/' . $action, $manager);
            if (!$authorize->isAllowed($app->getUser())) {
                throw new Forbidden('You cannot access this page.');
            }*/

            /* If everything is good, let's call the correct route. */
            $response = $this->$action();
            if ($response) {
                $this->setResponse($response);
            } else {
                $response = $this->getResponse();
                $response->addContent($this->getLayout());
            }
            Event::dispatch(new EventItem('controller/action/after', [
                'controller' => $this,
                'action' => $action,
                'response' => $response,
            ]));
            return $response;
        }

        /**
         * Get our global app state.
         *
         * @return App
         */
        public function getApp()
        {
            return $this->app;
        }

        /**
         * Set our global app state.
         *
         * @param App $app
         * @return IController
         */
        public function setApp(App $app)
        {
            $this->app = $app;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getRequest()
        {
            if ($this->request === null) {
                Event::dispatch(new EventItem('controller/request/before', [
                    'controller' => $this
                ]));
                $this->request = Request::createFromGlobal();
                Event::dispatch(new EventItem('controller/request/after', [
                    'controller' => $this,
                    'request' => $this->request
                ]));
            }
            return $this->request;
        }

        /**
         * {@inheritDoc}
         */
        public function setRequest(IRequest $request)
        {
            $this->request = $request;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getResponse()
        {
            if ($this->response === null) {
                Event::dispatch(new EventItem('controller/response/before', [
                    'controller' => $this
                ]));
                $this->response = new Response($this->getRequest()->getEnvironmentals(), $this->getApp()
                    ->get('config/status_code'));
                Event::dispatch(new EventItem('controller/response/after', [
                    'controller' => $this,
                    'response' => $this->response
                ]));
            }
            return $this->response;
        }

        /**
         * {@inheritDoc}
         */
        public function setResponse(IResponse $response)
        {
            $this->response = $response;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getLayout()
        {
            if ($this->layout === null) {
                Event::dispatch(new EventItem('controller/layout/before', [
                    'controller' => $this
                ]));
                $this->layout = new Layout;
                $this->layout->setController($this);
                Event::dispatch(new EventItem('controller/layout/after', [
                    'controller' => $this,
                    'layout' => $this->layout
                ]));
            }
            return $this->layout;
        }

        /**
         * {@inheritDoc}
         */
        public function setLayout(ILayout $layout)
        {
            $this->layout = $layout;
            return $this;
        }

        /**
         * Generate a pathed url.
         *
         * @param string $url
         * @param string $location
         * @return string
         */
        public function url($url = '', $location = '')
        {
            if (!$url) {
                return '/';
            }

            if (is_array($url)) {
                $parameters = $url;
                $url = array_shift($parameters);
                $url .= '?' . http_build_query($parameters, '', '&amp;');
            }

            if (strpos($url, '://')) {
                return $url;
            }

            if ($location) {
                $app = $this->getApp();
                $theme = $app->getTheme();
                $path = $app->getPath();
                $routes = $path->getRoutes('public');
                $paths = [];

                foreach ($routes as $route) {
                    $paths[$route . 'resources' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $location . DIRECTORY_SEPARATOR . $url] = DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $location . DIRECTORY_SEPARATOR . $url;
                    $paths[$route . 'resources' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $location . DIRECTORY_SEPARATOR . $url] = DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $location . DIRECTORY_SEPARATOR . $url;
                }
                foreach ($paths as $check => $source) {
                    if (is_readable($check)) {
                        return $source;
                    }
                }
            } else {
                $url = '/' . $url;
            }

            return $url;
        }

        /**
         * Set a redirect instead of rendering the page.
         *
         * @param string|array $redirect
         * @return Response
         */
        public function redirect($redirect = '')
        {
            $response = $this->getResponse();
            if (is_array($redirect)) {
                $parameters = $redirect;
                $redirect = array_shift($parameters);
                $redirect .= '?' . http_build_query($parameters);
            }
            $response->redirect($redirect);
            return $response;
        }

        /**
         * {@inheritDoc}
         */
        public function render()
        {
            $this->parsed = true;
            return $this->getResponse();
        }

    }
