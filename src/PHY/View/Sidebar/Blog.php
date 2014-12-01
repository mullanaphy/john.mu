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

    namespace PHY\View\Sidebar;

    use PHY\Model\Config;
    use PHY\View\AView;

    /**
     * Most recent blog posts.
     *
     * @package PHY\View\Sidebar\Blog
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Blog extends AView
    {

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            /**
             * @var \PHY\App $app
             */
            $app = $this->getLayout()->getController()->getApp();

            /**
             * @var \PHY\Model\User $user
             */
            $user = $app->getUser();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            /**
             * @var \PHY\Database\ICollection $collection
             */
            $collection = $manager->getCollection('Blog');
            $collection->limit(3);
            $visibility = $user->getVisibility();
            $visibility[] = '';
            $collection->where()->field('visible')->in($visibility);
            $collection->where()->field('deleted')->is(false);
            $collection->order()->by('published')->direction('desc');
            $this->setVariable('collection', $collection);
        }

    }
