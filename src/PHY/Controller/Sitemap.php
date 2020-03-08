<?php

    /**
     * john.mu
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to hi@john.mu so we can send you a copy immediately.
     */

    namespace PHY\Controller;

    /**
     * Sitemap
     *
     * @package PHY\Controller\Sitemap
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Sitemap extends AController
    {

        /**
         * There's only one end point so we're going to overwrite action so it doesn't do unnecessary database lookups
         * in relation to authorizations.
         *
         * @param string $method
         * @return \PHY\Http\IResponse $response
         */
        public function action($method = 'index')
        {
            $app = $this->getApp();
            $response = $this->getResponse();

            /** @var \PHY\Cache\ICache $cache */
            $cache = $app->get('cache/rendered');

            /** @var \PHY\Database\IDatabase $database */
            $database = $app->get('database');
            $manager = $database->getManager();

            if (!$sitemap = $cache->get('sitemap')) {
                $latest = false;
                $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

                $collection = $manager->getCollection('Blog');
                $collection->order()->by('updated')->direction('desc');
                foreach ($collection as $item) {
                    $sitemap .= "\t" . '<url>' . PHP_EOL;
                    $sitemap .= "\t\t" . '<loc>https://john.mu/blog/' . $item->slug . '</loc>' . PHP_EOL;
                    if ($item->updated) {
                        $lastmod = explode(' ', $item->updated)[0];
                        if (!$latest) {
                            $latest = $lastmod;
                        }
                        $sitemap .= "\t\t" . '<lastmod>' . $lastmod . '</lastmod>' . PHP_EOL;
                    }
                    $sitemap .= "\t" . '</url>';
                }

                $sitemap .= "\t" . '<url>' . PHP_EOL;
                $sitemap .= "\t\t" . '<loc>https://john.mu</loc>' . PHP_EOL;
                $sitemap .= "\t\t" . '<lastmod>' . ($latest
                        ?: date('Y-m-d')) . '</lastmod>' . PHP_EOL;
                $sitemap .= "\t\t" . '<changefreq>weekly</changefreq>' . PHP_EOL;
                $sitemap .= "\t\t" . '<priority>1.0</priority>' . PHP_EOL;
                $sitemap .= "\t" . '</url>' . PHP_EOL;

                $sitemap .= "\t" . '<url>' . PHP_EOL;
                $sitemap .= "\t\t" . '<loc>https://john.mu/contact</loc>' . PHP_EOL;
                $sitemap .= "\t\t" . '<priority>0.1</priority>' . PHP_EOL;
                $sitemap .= "\t" . '</url>' . PHP_EOL;

                $sitemap .= '</urlset>';
                $cache->set('sitemap', $sitemap);
            }

            $response->setHeader('Content-Type', 'text/xml; charset=utf-8');
            $response->setContent([$sitemap]);
            return $response;
        }

    }
