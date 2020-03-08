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
    use PHY\Event;
    use PHY\Event\Item as EventItem;
    use PHY\Variable\Obj;

    /**
     * Handles the hierarchy of the DOM and makes sure elements and their
     * children are rendered to the page.
     *
     * @package PHY\View\Layout
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Layout implements ILayout
    {

        protected $controller = null;
        protected $blocks = [];
        protected $variables = [];
        protected $rendered = false;

        /**
         * {@inheritDoc}
         */
        public function __toString()
        {
            return $this->toString();
        }

        /**
         * {@inheritDoc}
         */
        public function loadBlocks()
        {
            $configs = func_get_args();
            $app = $this->getController()->getApp();
            foreach ($configs as $key) {
                $file = false;
                foreach ($app->getPath()
                             ->getPaths('design' . DIRECTORY_SEPARATOR . $app->getTheme() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $key . '.json', 'design' . DIRECTORY_SEPARATOR . $app->getNamespace() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $key . '.json', 'design' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $key . '.json') as $check) {
                    if (is_file($check)) {
                        $file = $check;
                        break;
                    }
                }
                if (!$file) {
                    continue;
                }
                $FILE = fopen($file, 'r');
                $content = fread($FILE, filesize($file));
                fclose($FILE);
                $content = preg_replace(['#/\*.+?\*/#is'], '', $content);
                $content = json_decode($content);
                $content = (new Obj($content))->toArray();
                foreach ($content as $key => $value) {
                    $this->buildBlocks($key, $value);
                }
            }
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function block($block, array $config = null)
        {
            if ($config !== null) {
                if (array_key_exists($block, $this->blocks)) {
                    $this->blocks[$block]->setVariables($config);
                } else {
                    $this->buildBlocks($block, $config);
                }
            }
            return array_key_exists($block, $this->blocks)
                ? $this->blocks[$block]
                : null;
        }

        /**
         * {@inheritDoc}
         */
        public function setController(IController $controller)
        {
            $event = new EventItem('layout/controller/before', [
                'view' => $this,
                'controller' => $controller
            ]);
            Event::dispatch($event);
            $this->controller = $controller;
            $event->setName('layout/controller/after');
            Event::dispatch($event);
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function getController()
        {
            return $this->controller;
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            return (string)$this->block('layout');
        }

        /**
         * {@inheritDoc}
         */
        public function render()
        {
            $this->rendered = true;
            return $this->toString();
        }

        /**
         * {@inheritDoc}
         */
        public function buildBlocks($key, $config)
        {
            if (array_key_exists('viewClass', $config)) {
                if (strpos($config['viewClass'], '/') !== false) {
                    $viewClass = implode('\\', array_map('ucfirst', explode('/', $config['viewClass'])));
                } else {
                    $viewClass = ucfirst($config['viewClass']);
                }
                $class = $this->getController()->getApp()->getClass('View\\' . $viewClass);
                if (!$class) {
                    throw new Exception('Cannot find ' . $class . ' view class.');
                }
                $this->blocks[$key] = new $class($key, $config);
            } else {
                $this->blocks[$key] = new Block($key, $config);
            }
            $this->blocks[$key]->setLayout($this);
            if ($children = $this->blocks[$key]->getChildren()) {
                foreach ($children as $child => $values) {
                    $this->buildBlocks($child, $values);
                }
            }
            return $this;
        }

    }

