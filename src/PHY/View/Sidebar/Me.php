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

    use PHY\Model\Config as ConfigModel;
    use PHY\View\AView;

    /**
     * My little sidebar me.
     *
     * @package PHY\View\Sidebar\Me
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Me extends AView
    {

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            $app = $this->getLayout()->getController()->getApp();
            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $name = $manager->load(['key' => 'name'], new ConfigModel);
            $this->setVariable('name', $name->value);
        }

    }
