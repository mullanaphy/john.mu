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

    use PHY\Http\IRequest;

    /**
     * Handle RewriteRules. .htaccess is for suckers stuck on Apache.
     *
     * @package PHY\Model\Rewrite
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Rewrite extends Entity
    {

        protected static $_source = [
            'cacheable' => [
                ['request_method', 'request_url']
            ],
            'schema' => [
                'primary' => [
                    'table' => 'rewrite',
                    'columns' => [
                        'request_method' => 'variable',
                        'request_url' => 'variable',
                        'destination' => 'variable',
                        'redirect' => 'boolean',
                        'updated' => 'date',
                        'created' => 'date',
                        'deleted' => 'boolean'
                    ],
                    'id' => 'id',
                    'local' => [
                        'request' => 'UNIQUE INDEX (`request_method`, `request_url`)',
                        'email' => 'unique'
                    ]
                ]
            ]
        ];

        /**
         * Load a RewriteRule by its Request.
         *
         * @param IRequest $request
         * @return $this
         */
        public static function loadByRequest(IRequest $request)
        {
            return [
                'request_url' => $request->getUrl(),
                'request_method' => $request->getMethod()
            ];
        }

        /**
         * See if this is a redirect or not.
         *
         * @return boolean
         */
        public function isRedirect()
        {
            return $this->data['redirect'];
        }

    }
