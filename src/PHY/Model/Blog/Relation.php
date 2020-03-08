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

    namespace PHY\Model\Blog;

    use PHY\Model\Entity;

    /**
     * For blog posts so we can keep people updated on our standings.
     *
     * @package PHY\Model\Blog\Relation
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Relation extends Entity
    {

        protected static $_source = [
            'cacheable' => [
                'slug'
            ],
            'schema' => [
                'primary' => [
                    'table' => 'blog_relation',
                    'columns' => [
                        'slug' => 'variable',
                        'next' => 'variable',
                        'previous' => 'variable',
                    ],
                    'keys' => [
                        'local' => [
                            'slug' => 'unique',
                        ]
                    ]
                ]
            ]
        ];

    }
