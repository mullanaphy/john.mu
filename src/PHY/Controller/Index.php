<?php

    /**
     * jo.mu
     * LICENSE
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to license@phyneapple.com so we can send you a copy immediately.

     */

    namespace PHY\Controller;

    use PHY\Model\Config as ConfigModel;

    /**
     * Home page.
     *
     * @package PHY\Controller\Index
     * @category PHY\Phyneapple
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

            $head->setVariable('title', 'Blog');
            $head->setVariable('description', 'Read some of the most recent news and tidbits from John Mullanaphy.');

            /* @var \PHY\Model\User\Collection $collection */
            $collection = $manager->getCollection('Blog');
            $collection->where()->field('visible')->in($visibility);

            $content->setVariable('collection', $collection);
            $content->setTemplate('blog/content.phtml');

            $count = $collection->count();
            $request = $this->getRequest();
            if ($count > $limit = $request->get('limit', 10)) {
                $pageId = 1;
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
