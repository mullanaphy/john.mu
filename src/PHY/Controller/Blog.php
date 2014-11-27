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
     * to license@phyneapple.com so we can send you a copy immediately.
     *
     */

    namespace PHY\Controller;

    use PHY\Model\Blog as Model;
    use Michelf\Markdown;
    use PHY\Variable\Str;

    /**
     * Blog page.
     *
     * @package PHY\Controller\Blog
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
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

            /* @var \PHY\Database\IDatabase $database */
            $database = $app->get('database');
            $manager = $database->getManager();

            $request = $this->getRequest();
            $action = $request->getActionName();
            $user = $app->getUser();
            $visibility = $user->getVisibility();
            $visibility[] = '';

            if ($action !== '__index') {
                $model = new Model;
                $item = $manager->load(['slug' => $action], $model);
                if (!$item->exists() || !in_array($item->visible, $visibility)) {
                    return $this->redirect('/blog');
                }
                $content->setTemplate('blog/view.phtml');
                $content->setVariable('item', $item);
                $cache = $app->get('cache');
                if (!$description = $cache->get('blog/' . $item->id() . '/description')) {
                    $description = strip_tags(Markdown::defaultTransform((new Str(ucfirst($item->content)))->toShorten(160)));
                    $cache->set('blog/' . $item->id() . '/description', $description, 86400 * 31);
                }
                $head->setVariable('description', $description);

                if (!$post = $cache->get('blog/' . $item->id() . '/rendered')) {
                    $post = Markdown::defaultTransform($item->content);
                    $cache->set('blog/' . $item->id() . '/rendered', $post, 86400 * 31);
                }

                $content->setVariable('content', $post);
            } else {
                $head->setVariable('title', 'Blog');
                $head->setVariable('description', 'Come enjoy the mediocre writings, discussions, and any thoughts that I\'ve unfortunately shared with the world.');

                /* @var \PHY\Model\User\Collection $collection */
                $collection = $manager->getCollection('Blog');
                $collection->where()->field('visible')->in($visibility);

                $content->setVariable('collection', $collection);

                $count = $collection->count();
                $request = $this->getRequest();
                if ($count > $limit = $request->get('limit', 10)) {
                    $pages = ceil($count / $limit);
                    if ($action === 'page') {
                        $pageId = $action;
                        if (!$pageId) {
                            $pageId = 1;
                        } else if ($pageId > $pages) {
                            $pageId = $pages;
                        }
                    } else {
                        $pageId = 1;
                    }
                    $offset = ($pageId * $limit) - $limit;
                    $collection->limit($offset, $limit);
                    $content->setChild('blog/pagination', [
                        'viewClass' => 'pagination',
                        'limit' => $limit,
                        'total' => $count,
                        'pageId' => $pageId,
                        'url' => '/blog/page/[%i]'
                    ]);
                }
            }
        }

    }
