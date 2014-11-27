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

    /**
     * Parse a controller's name and then attempt to call
     * $controller/content.phtml
     *
     * @package PHY\View\Parser
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Parser extends AView
    {

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            $class = get_class($this->getLayout()->getController());
            $class = explode('\\', $class);
            $class = array_slice($class, 2);
            $variables = $this->getConfig();
            if (!array_key_exists('template', $variables)) {
                $variables['template'] = strtolower(implode('/', $class)) . '/content.phtml';
            }
            unset($variables['viewClass']);
            $this->setConfig($variables);
        }

    }
