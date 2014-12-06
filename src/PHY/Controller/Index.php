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

    use PHY\Model\Config as ConfigModel;

    /**
     * Home page.
     *
     * @package PHY\Controller\Index
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Index extends AController
    {

        /**
         * GET /
         */
        public function index_get()
        {

            $app = $this->getApp();

            /** @var \PHY\Cache\ICache $cache */
            $cache = $app->get('cache/rendered');

            /** @var \PHY\Database\IDatabase $database */
            $database = $app->get('database');
            $manager = $database->getManager();

            $limitConfig = $manager->load(['key' => 'limit'], new ConfigModel);
            $limit = $limitConfig->value
                ?: 5;

            $response = $this->getResponse();
            $request = $this->getRequest();

            $user = $app->getUser();
            $visibility = $user->getVisibility();
            $visibility[] = '';

            $actionName = $request->getActionName();
            $pageId = (int)$actionName;
            if (!$pageId) {
                $pageId = 1;
            }
            $cacheKey = 'html/index/blog/' . implode(',', $visibility) . '-' . $limit . '-' . $pageId;

            if (!$cachedPage = $cache->get($cacheKey)) {
                $layout = $this->getLayout();
                $head = $layout->block('head');
                $content = $layout->block('content');

                $head->setVariable('title', 'Current happenings of John Mullanaphy' . ($pageId > 1
                        ? ' (Page ' . $pageId . ')'
                        : ''));
                $head->setVariable('description', 'Recaps of my current life which usually involves nerdy hobbies.');

                /** @var \PHY\Model\User\Collection $collection */
                $cached = true;
                $count = $cache->get($cacheKey . '-count');
                if (!$collection = $cache->get($cacheKey . '-inner')) {
                    $cached = false;
                    $collection = $manager->getCollection('Blog');
                    $collection->where()->field('visible')->in($visibility);
                    $collection->order()->by('created')->direction('desc');
                    if (!is_numeric($count)) {
                        $count = $collection->count();
                    }
                }

                $content->setVariable('collection', $collection);
                $content->setVariable('count', $count);
                $content->setTemplate('blog/content.phtml');

                if ($count > $limit) {
                    $offset = ($pageId * $limit) - $limit;
                    if ($offset >= $count) {
                        return $this->redirect('/');
                    }
                    if (!$cached) {
                        $collection->limit($offset, $limit);
                        $cachedVersions = $cache->get('html/index/blog');
                        if (!$cachedVersions) {
                            $cachedVersions = [];
                        }
                        $cachedVersions[] = $cacheKey;
                        $cache->replace('html/index/blog', $cachedVersions);
                        $cache->set($cacheKey . '-inner', $collection->toArray());
                        $cache->set($cacheKey . '-count', $count);
                    }
                    $content->setChild('blog/pagination', [
                        'viewClass' => 'pagination',
                        'limit' => $limit,
                        'total' => $count,
                        'pageId' => $pageId,
                        'url' => '/page/[%i]'
                    ]);
                }

                $cachedPage = $layout->render();
                $cache->set($cacheKey, $cachedPage);
            }
            $response->setContent([$cachedPage]);
            return $response;
        }

    }
