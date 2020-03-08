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

    /**
     * Search box.
     *
     * @package PHY\View\Header\Searchbox
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Searchbox extends AView
    {

        /**
         * {@inheritDoc}
         */
        public function structure()
        {
            $search = $this->getLayout()->getController()->getRequest()->get('q', false);
            $this->setTemplate('core/sections/header/searchbox.phtml')->setVariable('search', $search);
        }

    }
