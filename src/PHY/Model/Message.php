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
     * For our contact page so my inbox doesn't get flooded with unnecessary rubbish.
     *
     * @package PHY\Model\Message
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Message extends Entity
    {

        protected static $_source = [
            'cacheable' => true,
            'schema' => [
                'primary' => [
                    'table' => 'message',
                    'columns' => [
                        'name' => 'variable',
                        'email' => 'variable',
                        'content' => 'text',
                        'alerted' => 'date',
                        'replied' => 'date',
                        'created' => 'date',
                        'updated' => 'date',
                        'read' => 'date',
                        'deleted' => 'boolean',
                    ],
                ],
            ],
        ];

    }
