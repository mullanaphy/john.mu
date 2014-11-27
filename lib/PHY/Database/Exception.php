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

    namespace PHY\Database;

    /**
     * For Database related exceptions.
     *
     * @package PHY\Database\Exception
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Exception extends \Exception
    {

        protected $query = '';

        /**
         * Set a query along with our exception.
         *
         * @param string $message
         * @param mixed $code
         * @param mixed $query
         * @param mixed $previous
         */
        public function __construct($message = '', $code = null, $query = '', $previous = null)
        {
            $this->query = $query;
            parent::__construct($message, $code, $previous);
        }

        /**
         * Get the query that caused the Exception.
         *
         * @return string
         */
        public function getQuery()
        {
            return $this->query;
        }

    }
