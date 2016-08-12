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

    namespace PHY;

    use PHY\Component\IComponent;
    use PHY\Component\Registry;
    use PHY\Controller\IController;
    use PHY\Http\Exception as HttpException;
    use PHY\Http\IRequest;
    use PHY\Http\Response;
    use PHY\Model\IUser;
    use PHY\Variable\Str;
    use PHY\View\Layout;

    /**
     * Core APP class. This holds all global states and pieces everything
     * together.
     *
     * @package PHY\APP
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     * @todo Make this make sense. Try and break up what should be global and what should be in the registry class.
     */
    class App
    {

        private $namespace = 'default';
        private $components = [];
        private $environment = 'development';
        private $path = null;
        private $debugger = null;
        private $rootDirectory = '';
        private $publicDirectory = '';
        private $user = null;
        private $theme = '';
        private $classNamespaces = [];
        private $loaddedClassNamespaces = [];
        private $xsrfId = '';

        /**
         * Return a value from the Registry if it exists.
         *
         * @param string $key
         * @return mixed|null
         */
        public function get($key)
        {
            if ($component = $this->parseComponent($key)) {
                return $component[0]->get($component[1]);
            } else {
                if ($this->hasComponent($key)) {
                    return $this->getComponent($key);
                } else {
                    return $this->getRegistry()->get($key);
                }
            }
        }

        /**
         * Set a Registry value. If the value already exists then it will fail
         * and a warning will be printed if $graceful is false.
         *
         * @param string $key
         * @param mixed $value
         * @return mixed
         * @throws Exception
         */
        public function set($key, $value)
        {
            if (!is_string($key)) {
                throw new Exception('A registry key must be a string.');
            } else {
                if ($component = $this->parseComponent($key)) {
                    return $component[0]->set($component[1], $value);
                } else {
                    return $this->getRegistry()->set($key, $value);
                }
            }
        }

        /**
         * Delete this registry key if it exists.
         *
         * @param string $key
         * @param bool $graceful
         * @return bool
         */
        public function delete($key = null, $graceful = false)
        {
            if ($component = $this->parseComponent($key)) {
                return $component[0]->delete($component[1]);
            } else {
                return $this->getRegistry()->delete($key);
            }
        }

        /**
         * Check to see if a key exists. Useful if the ::get you might be using can be
         * false\null and you want to make sure that it was set false and not just a null.
         *
         * @param string $key
         * @return bool
         */
        public function has($key = null)
        {
            if ($component = $this->parseComponent($key)) {
                return $component[0]->has($component[1]);
            } else {
                return $this->getRegistry()->has($key);
            }
        }

        /**
         * Set our registry class to use for our App.
         *
         * @param IComponent $registry
         * @return $this
         */
        public function setRegistry(IComponent $registry)
        {
            $this->addComponent($registry);
            return $this;
        }

        /**
         * Grab our Registry. If one hasn't been defined, we'll start a new one.
         *
         * @return IComponent
         */
        public function getRegistry()
        {
            if (!array_key_exists('registry', $this->components)) {
                $this->addComponent(new Registry);
            }
            return $this->components['registry'];
        }

        /**
         * Set our Path class to use in our App.
         *
         * @param Path $path
         * @return $this
         */
        public function setPath(Path $path)
        {
            $this->path = $path;
            return $this;
        }

        /**
         * Return our global Path.
         *
         * @return Path
         */
        public function getPath()
        {
            if ($this->path === null) {
                $this->setPath(new Path([
                    'root' => $this->getRootDirectory(),
                    'public' => $this->getPublicDirectory(),
                ]));
            }
            return $this->path;
        }

        /**
         * Set  our global Debugger.
         *
         * @param IDebugger $debugger
         * @return $this
         */
        public function setDebugger(IDebugger $debugger)
        {
            $this->debugger = $debugger;
            return $this;
        }

        /**
         * Grab our global Debugger class.
         *
         * @return IDebugger
         */
        public function getDebugger()
        {
            if ($this->debugger === null) {
                $this->setDebugger(new Debugger);
            }
            return $this->debugger;
        }

        /**
         * Change which namespace to use.
         *
         * @param string $namespace
         * @return $this
         */
        public function setNamespace($namespace)
        {
            $this->namespace = $namespace;
            return $this;
        }

        /**
         * Get the currently defined namespace to use.
         *
         * @return string
         */
        public function getNamespace()
        {
            return $this->namespace;
        }

        /**
         * Change which namespace to use.
         *
         * @param string $theme
         * @return $this
         */
        public function setTheme($theme)
        {
            $this->theme = $theme;
            return $this;
        }

        /**
         * Get the currently defined theme to use.
         *
         * @return string
         */
        public function getTheme()
        {
            return $this->theme
                ?: $this->getNamespace();
        }

        /**
         * Set whether we're a development app or in production.
         *
         * @param string $environment
         * @return $this
         */
        public function setEnvironment($environment)
        {
            $this->environment = $environment;
            return $this;
        }

        /**
         * See whether we're a development app or in production.
         *
         * @return boolean
         */
        public function isDevelopment()
        {
            return $this->get('config/' . $this->environment . '/development');
        }

        /**
         * Check for a programmed component. Things like databases, cache, or
         * config files.
         *
         * @param string $component
         * @return IComponent or (bool)false if Component isn't found
         */
        private function parseComponent($component)
        {
            if (strpos($component, '/') !== false) {
                $key = explode('/', $component);
                $component = array_shift($key);
                $key = implode('/', $key);
            } else {
                $key = 'default';
            }
            if ($this->hasComponent($component)) {
                return [$this->getComponent($component), $key];
            } else {
                $className = $this->getClass('Component\\' . ucfirst($component));
                if ($className) {
                    $class = new $className;
                    if ($class instanceof IComponent) {
                        $this->addComponent($class);
                        return [$this->getComponent($component), $key];
                    }
                }
            }
            return false;
        }

        /**
         * Get a component if it exists.
         *
         * @param string $key
         * @return IComponent|bool
         */
        public function getComponent($key)
        {
            $key = strtolower($key);
            return array_key_exists($key, $this->components)
                ? $this->components[$key]
                : false;
        }

        /**
         * See if a component exists.
         *
         * @param string $key
         * @return bool
         */
        public function hasComponent($key)
        {
            return array_key_exists(strtolower($key), $this->components);
        }

        /**
         * Add a component to our App.
         *
         * @param IComponent $component
         * @return $this
         */
        public function addComponent(IComponent $component)
        {
            $component->setApp($this);
            $this->components[$component->getName()] = $component;
            return $this;
        }

        /**
         * Set our user.
         *
         * @param IUser $user
         * @return $this
         */
        public function setUser(IUser $user)
        {
            $this->user = $user;
            return $this;
        }

        /**
         * Get our logged in user.
         *
         * @return IUser
         */
        public function getUser()
        {
            if ($this->user === null) {
                $this->setUser($this->get('session/user'));
            }
            return $this->user;
        }

        /**
         * Render our app.
         *
         * @param IRequest $request
         * @return null
         * @throws Exception
         */
        public function render(IRequest $request)
        {
            /* Look for a rewrite rule */
            try {
                set_error_handler(function ($number, $message, $file, $line) {
                    throw new \ErrorException($message, $number, $number, $file, $line);
                });
                //$manager = $this->get('database/default.manager');
                /* @var \PHY\Model\Rewrite $rewrite */
                //$rewrite = $manager->getModel('rewrite');
                //$manager->load($rewrite::loadByRequest($request), $rewrite);
                if (false && $rewrite->exists()) {
                    if ($rewrite->isRedirect()) {
                        $response = new Response($request->getEnvironmentals(), $this->get('config/status_code'));
                        $response->redirect($rewrite->destination, $rewrite->redirect);
                        $response->renderHeaders();
                        exit;
                    } else {
                        $path = $rewrite->destination;
                    }
                } else {
                    $path = $request->getUrl();
                }
                $pathParameters = explode('/', strtolower(trim($path, '/')));
                if (count($pathParameters) >= 2) {
                    $controllerName = array_shift($pathParameters);
                    $controllerClass = ucfirst($controllerName);
                    $actionName = array_shift($pathParameters);
                    $method = $actionName;
                    if (count($pathParameters)) {
                        $parameters = [
                            [],
                            []
                        ];
                        $i = 1;
                        foreach ($pathParameters as $key) {
                            $parameters[$i === 0
                                ? $i = 1
                                : $i = 0][] = $key;
                        }
                        if (count($parameters[1]) !== count($parameters[0])) {
                            $parameters[1][] = $parameters[0][count($parameters[0]) - 1];
                            $parameters[0][count($parameters[0]) - 1] = '__slug';
                        }
                        $request->addParameters(array_combine($parameters[0], $parameters[1]));
                    }
                } else {
                    if (count($pathParameters)) {
                        $controllerClass = current($pathParameters);
                        $controllerName = $controllerClass;
                        if (!$controllerClass) {
                            $controllerClass = 'Index';
                            $controllerName = 'index';
                        }
                        $method = 'index';
                        $actionName = '__index';
                    } else {
                        $controllerClass = 'Index';
                        $controllerName = 'index';
                        $method = 'index';
                        $actionName = '__index';
                    }
                }

                $request->setControllerName($controllerName);
                $request->setActionName($actionName);
                /* @var \PHY\Controller\IController $controller */
                $_ = $this->getClass('Controller\\' . ucfirst($controllerClass));
                if ($_) {
                    $controller = new $_($this);
                    if (!($controller instanceof IController)) {
                        $controller = false;
                    }
                } else {
                    $controller = false;
                }
                if (!$controller) {
                    throw new HttpException\NotFound('Seems I couldn\'t find your requests controller "' . $controllerClass . '". Blame the programmer, they almost definitely did it. Even if you put in the wrong url, just blame them. They\'re used to it!');
                }

                /*
                 * Let's do some XSRF stuffs.
                 */
                /* @var $cookieManager \PHY\Component\Cookie */
                $cookieManager = $this->get('cookie');
                $this->xsrfId = $cookieManager->get('xsrfId');
                if ($request->getMethod() !== 'GET') {
                    if ($this->xsrfId !== $request->get('xsrfId')) {
                        throw new HttpException\Forbidden('XSRF ID does not match what was supplied. Sorry, but no dice.');
                    } else {
                        $accepts = explode(', ', $request->getEnvironmental('HTTP_ACCEPT', 'text/plain'));
                        $ajax = false;
                        $ajaxTypes = ['application/json', 'application/javascript', 'text/javascript'];
                        foreach ($accepts as $accept) {
                            if (in_array($accept, $ajaxTypes)) {
                                $ajax = true;
                                break;
                            }
                        }
                        if (!$ajax) {
                            $this->xsrfId = Str::random(16)->get();
                        }
                    }
                }
                if (!$this->xsrfId) {
                    $this->xsrfId = Str::random(16)->get();
                }
                $cookieManager->set('xsrfId', $this->xsrfId);

                $controller->setRequest($request);

                $layout = new Layout;
                $layout->setController($controller);
                $layout->loadBlocks('default', $controllerName . '/' . $method);
                $controller->setLayout($layout);

                $controller->action($method);
                $response = $controller->render();
            } catch (\Exception $exception) {
                /* @var \PHY\Controller\Error $controller */
                $controller = new Controller\Error($this);
                if ($exception instanceof Database\Exception) {
                    $controller->setMessage('Sorry, yet there was an issue trying to connect to our database. Please try again in a bit');
                } else if ($exception instanceof HttpException) {
                    $controller->httpException($exception);
                } else if ($exception instanceof Exception) {
                    $controller->setMessage('Sorry, but something happened Phyneapple related. Could have been us or our framework. Looking in to it...');
                } else if ($exception instanceof \ErrorException) {
                    $controller->setMessage('Okay, I got this one, seems there is an issue related to the code itself. Hopefully the developer is logging these exceptions.');
                } else {
                    $controller->setMessage('Seems there was general error. We are checking it out.');
                }
                $controller->setException($exception);
                $controller->action('index');
                $response = $controller->render();
            }

            $response->renderHeaders();
            if (!$response->isRedirect()) {
                $response->renderContent();
            }
        }

        /**
         * Set our root directory.
         *
         * @param string $dir
         * @return $this
         */
        public function setRootDirectory($dir)
        {
            $this->rootDirectory = $dir;
            return $this;
        }

        /**
         * Get our root directory, this is the base of everything.
         *
         * @return string
         */
        public function getRootDirectory()
        {
            return $this->rootDirectory;
        }

        /**
         * Set our root directory.
         *
         * @param string $dir
         * @return $this
         */
        public function setPublicDirectory($dir)
        {
            $this->publicDirectory = $dir;
            return $this;
        }

        /**
         * Get our public folder's base path.
         *
         * @return string
         */
        public function getPublicDirectory()
        {
            return $this->publicDirectory;
        }

        /**
         * Register a class namespace for dynamic class loading.
         *
         * @param string $namespace
         * @param bool $prepend
         * @return $this
         */
        public function addClassNamespace($namespace, $prepend = false)
        {
            /*
             * Instead of using array_unshift, I want to make sure all namespaces are unique so I need the namespace to
             * also be the key.
             */
            if ($prepend) {
                $namespaces = $this->classNamespaces;
                $this->classNamespaces = [
                    $namespace => $namespace
                ];
                foreach ($namespaces as $namespace) {
                    $this->classNamespaces[$namespace] = $namespace;
                }
            } else {
                $this->classNamespaces[$namespace] = $namespace;
            }
            return $this;
        }

        /**
         * Remove a class namespace.
         *
         * @param string $namespace
         * @return $this
         */
        public function removeClassNamespace($namespace)
        {
            unset($this->classNamespaces[$namespace]);
            return $this;
        }

        /**
         * Set all of the namespaces in one shot.
         *
         * @param array $namespaces
         * @return $this
         */
        public function setClassNamespaces(array $namespaces = [])
        {
            $this->classNamespaces = $namespaces;
            return $this;
        }

        /**
         * Dynamically look for a class name based on our registered classes.
         *
         * @param string $className
         * @return string|false
         */
        public function getClass($className)
        {
            if (!array_key_exists($className, $this->loaddedClassNamespaces)) {
                $classes = $this->classNamespaces;
                $classes[] = 'PHY';
                foreach ($classes as $class) {
                    if (class_exists('\\' . $class . '\\' . $className)) {
                        $this->loaddedClassNamespaces[$className] = '\\' . $class . '\\' . $className;
                        return $this->loaddedClassNamespaces[$className];
                    }
                }
                $this->loaddedClassNamespaces[$className] = false;
            }
            return $this->loaddedClassNamespaces[$className];
        }

        /**
         * Grab our current session's xsrfId.
         *
         * @return string
         */
        public function getXsrfId()
        {
            return $this->xsrfId;
        }
    }
