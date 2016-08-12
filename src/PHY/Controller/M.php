<?php

    /**
     * jo.mu
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to john@jo.mu so we can send you a copy immediately.
     */

    namespace PHY\Controller;

    /**
     * Minifier
     *
     * @package PHY\Controller\M
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class M extends AController
    {
        private static $contentTypes = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'text/javascript; charset=utf-8',
        ];

        /**
         * This is where we'll actually minify our sources.
         *
         * @param string $method
         * @return \PHY\Http\IResponse $response
         */
        public function action($method = 'index')
        {
            /** @var \MatthiasMullie\Minify\Minify $minifier */
            if (isset(self::$contentTypes[$method])) {
                $type = $method;
            } else {
                $type = 'css';
            }
            $app = $this->getApp();
            $request = $this->getRequest();
            $response = $this->getResponse();
            $response->setHeader('Content-Type', self::$contentTypes[$type]);

            /** @var \PHY\Cache\ICache $cache */
            $cache = $app->get('cache/resources');

            $path = explode('/m/' . $type . '/', $request->getUrl());
            array_shift($path);
            $path = implode('', $path);

            $generated = $app->getRootDirectory() . 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'minified_' . md5($path);
            $content = $cache->get($path);
            if (!is_file($generated) || md5($content) !== file_get_contents($generated)) {
                $class = '\MatthiasMullie\Minify\\' . strtoupper($type);
                $files = explode(',', $path);
                $file = $app->getPublicDirectory() . $this->url(array_shift($files), $type);
                if (!is_readable($file)) {
                    $minifier = new $class('!1;');
                } else {
                    $minifier = new $class(file_get_contents($file));
                }
                if ($files) {
                    $root = $app->getPublicDirectory();
                    foreach ($files as $file) {
                        $file = $root . $this->url($file, $type);
                        if (is_readable($file)) {
                            $minifier->add(file_get_contents($file));
                        }
                    }
                }
                $content = $minifier->minify();
                $cache->set($path, $content);
                if (file_exists($generated)) {
//                    unlink($generated);
                } else {
                    touch($generated);
                }
                file_put_contents($generated, md5($content));
            }
            if ($content === '!1;') {
                $response->setStatusCode(404);
                return $response;
            }

            $lastModified = filemtime($generated);
            $etagFile = file_get_contents($generated);
            $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
            $response->setHeader('Etag', $etagFile);
            $response->setHeader('Cache-Control', 'public');

            $ifNotModifiedSince = $request->getEnvironmental('HTTP_IF_NOT_MODIFIED_SINCE', false);
            $etagHeader = $request->getEnvironmental('HTTP_IF_NONE_MATCH', false);

            if (strtotime($ifNotModifiedSince) === $lastModified || $etagHeader === $etagFile) {
                $response->setStatusCode(304);
                return $response;
            }

            $response->setContent([$content]);
            $response->setCompression(true);
            return $response;
        }

    }
