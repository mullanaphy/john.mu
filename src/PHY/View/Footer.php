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
    use PHY\Model\Config;

    /**
     * Footer block.
     *
     * @package PHY\View\Footer
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Footer extends AView
    {

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            $class = get_class($this->getLayout());
            $class = explode('\\', $class);
            $class = array_slice($class, 2)[0];
            $app = $this->getLayout()->getController()->getApp();
            $path = $app->getPath();
            try {
                $cache = $app->get('cache');
            } catch (\Exception $e) {
                $cache = new CacheLocal;
            }
            $theme = $app->getTheme();
            $key = $theme . '/' . $class . '/block/core/footer';
            if (!($files = $cache->get($key))) {
                $_files = $this->getVariable('files');
                $files = [
                    'css' => [],
                    'js' => []
                ];
                $merge = [];
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
                $root = $app->getPublicDirectory();
                foreach (array_keys($_files) as $type) {
                    foreach ($_files[$type] as $file) {
                        if (is_array($file) || is_object($file)) {
                            $file = (array)$file;
                            $sourceFile = $file[$defaults['key'][$type]];
                            if (strpos($sourceFile, '?') !== false) {
                                $sourceFile = explode('?', $sourceFile)[0];
                            }
                            $source = false;
                            foreach ($path->getPaths('public' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sourceFile), 'public' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sourceFile)) as $_source) {
                                if (is_file($_source)) {
                                    $source = $_source;
                                    break;
                                }
                            }
                            if (!$source) {
                                continue;
                            }
                            $file[$defaults['key'][$type]] = str_replace(DIRECTORY_SEPARATOR, '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $source));
                            $files[$type][] = array_merge($defaults[$type], $file);
                            continue;
                        } else {
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
                }
                foreach ($merge as $type => $items) {
                    foreach ($items as $item => $time) {
                        $files[$type][] = array_merge($defaults[$type], [
                            $defaults['key'][$type] => $item
                        ]);
                    }
                }
            }
            $event = new EventItem('block/core/footer', [
                'files' => $files,
                'xsrfId' => false
            ]);
            Event::dispatch($event);
            $files = $event->files;
            $this->setTemplate('core/sections/footer.phtml')->setVariable('js', $files['js']);
        }

        /**
         * Add files to the footer.
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
