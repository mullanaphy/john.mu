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
     * Head block.
     *
     * @package PHY\View\Head
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Head extends AView
    {

        const GOOGLE_ANALYTICS_KEY = 'block/core/head/googleAnalytics';

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            /** @var \PHY\Controller\IController $controller */
            $controller = $this->getLayout()->getController();

            /** @var \PHY\App $app */
            $app = $controller->getApp();

            /** @var \PHY\Cache\ICache $cache */
            $cache = $app->get('cache');

            $class = get_class($this->getLayout());
            $class = explode('\\', $class);
            $class = array_slice($class, 2)[0];

            $theme = $app->getTheme();
            $_files = $this->getVariable('files');

            $filesHash = [];
            if (array_key_exists('css', $_files)) {
                foreach ($_files['css'] as $file) {
                    $filesHash[] = $file;
                }
            }
            if (array_key_exists('js', $_files)) {
                foreach ($_files['js'] as $file) {
                    $filesHash[] = $file;
                }
            }
            $filesHash = $theme . '/' . $class . '/block/core/head/' . md5(implode('', $filesHash));

            if (!($files = $cache->get($filesHash))) {
                $files = [
                    'css' => [],
                    'js' => [],
                ];

                $defaults = [
                    'css' => [
                        'rel' => 'stylesheet',
                        'type' => 'text/css',
                    ],
                    'js' => [
                        'type' => 'text/javascript',
                    ],
                    'key' => [
                        'css' => 'href',
                        'js' => 'src',
                    ],
                ];
                $merge = [];
                $root = $app->getPublicDirectory();
                foreach (array_keys($_files) as $type) {
                    foreach ($_files[$type] as $file) {
                        if (substr($file, 0, 4) === 'http' || substr($file, 0, 2) === '//') {
                            $files[$type][] = array_merge($defaults[$type], [
                                $defaults['key'][$type] => $file,
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
                            $defaults['key'][$type] => $item,
                        ]);
                    }
                }
                $cache->set($filesHash, $files);
            }
            $event = new EventItem('block/core/head', [
                'files' => $files,
                'xsrfId' => $app->getXsrfId(),
            ]);
            Event::dispatch($event);
            $files = $event->files;
            $this->setTemplate('core/sections/head.phtml')
                ->setVariable('css', $files['css'])
                ->setVariable('js', $files['js'])
                ->setVariable('xsrfId', $event->xsrfId);

            /** @var \PHY\Database\IDatabase $database */
            $googleAnalytics = $cache->get(self::GOOGLE_ANALYTICS_KEY);
            if (!$googleAnalytics) {
                $database = $app->get('database');
                $manager = $database->getManager();
                /** @var \PHY\Model\Config $googleAnalytics */
                $googleAnalytics = $manager->load(['key' => 'googleAnalytics'], new Config)->value;
                $cache->set(self::GOOGLE_ANALYTICS_KEY, $googleAnalytics, 3600);
            }

            if ($googleAnalytics) {
                $event = new EventItem(self::GOOGLE_ANALYTICS_KEY, [
                    'value' => $googleAnalytics->value,
                ]);
                Event::dispatch($event);
                $this->setVariable('googleAnalytics', $event->value);
            }
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
