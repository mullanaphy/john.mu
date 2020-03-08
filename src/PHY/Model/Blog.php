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
     * to hi@john.mu so we can send you a copy immediately.
     */

    namespace PHY\Model;

    /**
     * For blog posts so we can keep people updated on our standings.
     *
     * @package PHY\Model\Blog
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Blog extends Entity
    {

        protected static $_source = [
            'cacheable' => [
                'slug'
            ],
            'schema' => [
                'primary' => [
                    'table' => 'blog',
                    'columns' => [
                        'type' => 'variable',
                        'slug' => 'variable',
                        'author_id' => 'variable',
                        'title' => 'variable',
                        'content' => 'text',
                        'tags' => 'variable',
                        'created' => 'date',
                        'updated' => 'date',
                        'published' => 'date',
                        'visible' => [
                            'nobody',
                            'super-admin',
                            'admin',
                            'regular',
                            'anonymous'
                        ],
                        'deleted' => 'boolean'
                    ],
                    'keys' => [
                        'local' => [
                            'slug' => 'unique',
                            'author_id' => 'index',
                        ]
                    ]
                ]
            ]
        ];

    }
