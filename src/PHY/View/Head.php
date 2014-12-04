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

    use PHY\Event;
    use PHY\Event\Item as EventItem;

    /**
     * Head block.
     *
     * @package PHY\View\Head
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Head extends AView
    {

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            $class = get_class($this->getLayout());
            $class = explode('\\', $class);
            $class = array_slice($class, 2)[0];
            $live = false;
            $controller = $this->getLayout()->getController();
            $app = $controller->getApp();
            $request = $controller->getRequest();
            $path = $app->getPath();
            $cache = $app->get('cache');
            $theme = $app->getTheme();
            $key = $theme . '/' . $class . '/block/core/head';
            if (!($files = $cache->get($key))) {
                $_files = $this->getVariable('files');
                $files = [
                    'css' => [],
                    'js' => []
                ];

                $defaults = [
                    'css' => [
                        'rel' => 'stylesheet',
                        'type' => 'text/css'
                    ],
                    'js' => [
                        'type' => 'text/javascript'
                    ],
                    'key' => [
                        'css' => 'href',
                        'js' => 'src'
                    ]
                ];
                $merge = [];
                $root = $app->getPublicDirectory();
                foreach (array_keys($_files) as $type) {
                    foreach ($_files[$type] as $file) {
                        if (substr($file, 0, 4) === 'http' || substr($file, 0, 2) === '//') {
                            $files[$type][] = array_merge($defaults[$type], [
                                $defaults['key'][$type] => $file
                            ]);
                        } else {
                            $sourceFile = $file;
                            if (strpos($sourceFile, '?') !== false) {
                                $sourceFile = explode('?', $sourceFile)[0];
                            }
                            $source = $this->url($sourceFile, $type);
                            if (!is_readable($root . DIRECTORY_SEPARATOR . $source)) {
                                continue;
                            }
                            $merge[$type]['/m/' . $type . '/' . $sourceFile] = filemtime($root . DIRECTORY_SEPARATOR . $source);
                        }
                    }
                }
                foreach ($merge as $type => $items) {
                    foreach ($items as $item => $time) {
                        $files[$type][] = array_merge($defaults[$type], [
                            $defaults['key'][$type] => $item
                        ]);
                    }
                }
                $cache->set($theme . '/' . $class . '/block/core/head', $files);
            }
            $event = new EventItem('block/core/head', [
                'files' => $files,
                'xsrfId' => $app->get('cookie')->get('xsrfId', false)
            ]);
            Event::dispatch($event);
            $files = $event->files;
            $this->setTemplate('core/sections/head.phtml')
                ->setVariable('css', $files['css'])
                ->setVariable('js', $files['js'])
                ->setVariable('xsrfId', $event->xsrfId);
        }

        /**
         * Add files to the header.
         *
         * @param string [, ...] $files
         * @return $this
         */
        public function add()
        {
            $files = func_get_args();
            $_files = $this->getVariable('files');
            foreach ($files as $file) {
                if (is_array($file)) {
                    call_user_func_array([$this, 'add'], $file);
                } else {
                    $extension = explode('.', $file);
                    $_files[$extension[count($extension) - 1]][] = $file;
                }
            }
            $this->setVariable('files', $_files);
            return $this;
        }

    }
