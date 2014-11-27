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

    namespace PHY\Model;

    /**
     * Get the currently running sites config data and such nots.
     *
     * @package PHY\Model\Site
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Site extends Entity
    {

        protected static $_source = [
            'cacheable' => true,
            'schema' => [
                'primary' => [
                    'table' => 'authorize',
                    'columns' => [
                        'theme' => 'variable',
                        'medium' => 'variable',
                        'development' => 'boolean',
                        'updated' => 'date',
                        'created' => 'date',
                        'deleted' => 'boolean'
                    ],
                    'filler' => [
                        'theme' => 'default',
                        'medium' => 'www',
                        'development' => true,
                        'updated' => '0000-00-00 00:00:00',
                        'created' => '0000-00-00 00:00:00',
                        'deleted' => false
                    ]
                ]
            ]
        ];

    }
