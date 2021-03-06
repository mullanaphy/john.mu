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
     * to license@phyneapple.com so we can send you a copy immediately.
     *
     */

    namespace PHY\Controller;

    use PHY\Helper;
    use PHY\Model\Blog as Model;

    /**
     * Blog page.
     *
     * @package PHY\Controller\Blog
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Blog extends AController
    {

        /**
         * GET /blog
         */
        public function index_get()
        {
            $layout = $this->getLayout();
            $head = $layout->block('head');
            $content = $layout->block('content');

            $app = $this->getApp();
            $request = $this->getRequest();
            $action = $request->getActionName();

            $user = $app->getUser();
            $visibility = $user->getVisibility();
            $visibility[] = '';

            /** @var \PHY\Cache\ICache $cache */
            $cache = $app->get('cache/rendered');

            $cacheKey = 'html/blog/' . $action . '/' . md5(implode(',', $visibility));
            if (!$cached = $cache->get($cacheKey)) {

                /** @var \PHY\Database\IDatabase $database */
                $database = $app->get('database');
                $manager = $database->getManager();

                /** @var Model $item */
                $item = $manager->load(['slug' => $action], new Model);
                if (!$item->exists() || !in_array($item->visible, $visibility)) {
                    return $this->redirect('/');
                }

                $cachedData = Helper::getRenderedBlogPost($item, $cache);
                
                $head->setVariable('title', $item->title . ' by John Mullanaphy');
                $head->setVariable('description', $cachedData->description);
                $head->setVariable('ogTitle', $item->title);
                $head->setVariable('ogUrl', 'https://john.mu/blog/' . $item->slug);
                $head->add($this->url('highlight.css', 'css'));
                if (is_file($app->getPublicDirectory() . DIRECTORY_SEPARATOR . 'media/blog/' . $item->slug . DIRECTORY_SEPARATOR . 'thumbnail.jpg')) {
                    $head->setVariable('ogImage', 'https://john.mu/media/blog/' . $item->slug . '/thumbnail.jpg');
                }

                $content->setTemplate('blog/view.phtml');
                $content->setVariable('item', $item);
                $content->setVariable('content', $cachedData->post);
    
                /** @var Model\Relation $relation */
                $relation = $manager->load(['slug' => $item->slug], new Model\Relation);
                if ($relation->previous) {
                    $content->setVariable('previous', $relation->previous);
                }
                if ($relation->next) {
                    $content->setVariable('next', $relation->next);
                }

                $cached = $layout->render();
                $cache->set($cacheKey, $cached);
            }
            $response = $this->getResponse();
            $response->setContent([$cached]);
            return $response;
        }

    }
