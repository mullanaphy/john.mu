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

    namespace PHY\View\Header;

    use PHY\View\AView;
    use PHY\Event;
    use PHY\Event\Item as EventItem;

    /**
     * Header menu links.
     *
     * @package PHY\View\Header\Menu
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Menu extends AView
    {

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            $controller = $this->getLayout()->getController();
            $request = $controller->getRequest();
            $links = $this->getVariable('links');
            $event = new EventItem('block/core/menu', [
                'links' => $links
            ]);
            Event::dispatch($event);
            $links = $event->links;
            $this->setTemplate('core/sections/header/menu.phtml')->setVariable('links', $links)
                ->setVariable('controllerName', $request->getControllerName())
                ->setVariable('actionName', $request->getActionName());
        }

    }
