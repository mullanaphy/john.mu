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

    namespace PHY\Cron;

    /**
     * Convert and match Cron expressions.
     *
     * @package PHY\Cron\Expr
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Expr
    {

        const STATUS_PENDING = 'pending';
        const STATUS_RUNNING = 'running';
        const STATUS_SUCCESS = 'success';
        const STATUS_MISSED = 'missed';
        const STATUS_ERROR = 'error';

        protected $expr = '';
        protected $match = false;
        protected $created;
        protected $scheduled;

        protected static $_numerics = [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6
        ];

        /**
         * Set an expression on load.
         *
         * @param string $expr
         */
        public function __construct($expr = '')
        {
            $this->created = new \DateTime;
            $this->scheduled = new \DateTime;
            if ($expr) {
                $this->setExpr($expr);
            }
        }

        /**
         * Set an expression.
         *
         * @param string $expr
         * @return $this
         * @throws Exception
         */
        public function setExpr($expr)
        {
            $e = preg_split('#\s+#', $expr, null, PREG_SPLIT_NO_EMPTY);
            if (sizeof($e) < 5 || sizeof($e) > 6) {
                throw new Exception('Invalid cron expression: ' . $expr . '.');
            }
            $this->expr = $e;
            return $this;
        }

        /**
         * Grab our expression.
         *
         * @return string
         */
        public function getExpr()
        {
            return $this->expr;
        }

        /**
         * Checks the observer's cron expression against time
         *
         * Supports $this->setCronExpr('* 0-5,10-59/5 2-10,15-25 january-june/2 mon-fri')
         *
         * @param mixed $time
         * @return bool
         */
        public function check($time)
        {
            $e = $this->getExpr();
            if (!$e || !$time) {
                return false;
            }
            if (!is_numeric($time)) {
                $time = strtotime($time);
            }
            $d = getdate($time);
            $match = $this->match($e[0], $d['minutes']) && $this->match($e[1], $d['hours']) && $this->match($e[2], $d['mday']) && $this->match($e[3], $d['mon']) && $this->match($e[4], $d['wday']);
            if ($match) {
                $this->matched = true;
                $this->created = new \DateTime('Y-m-d H:i:s');
                $this->scheduled = new \DateTime('Y-m-d H:i:s');
            }
            return $match;
        }

        /**
         * Match an exception.
         *
         * @param string $expr
         * @param int $num
         * @return bool
         * @throws Exception
         */
        public function match($expr, $num)
        {
            // handle ALL match
            if ($expr === '*') {
                return true;
            }

            // handle multiple options
            if (strpos($expr, ',') !== false) {
                foreach (explode(',', $expr) as $e) {
                    if ($this->match($e, $num)) {
                        return true;
                    }
                }
                return false;
            }

            if (strpos($expr, '/') !== false) {
                $e = explode('/', $expr);
                if (sizeof($e) !== 2) {
                    throw new Exception('Invalid cron expression, expecting "match/modulus": ' . $expr . '. #');
                }
                if (!is_numeric($e[1])) {
                    throw new Exception('Invalid cron expression, expecting numeric modulus: ' . $expr . '. #');
                }
                $expr = $e[0];
                $mod = $e[1];
            } else {
                $mod = 1;
            }

            // handle all match by modulus
            if ($expr === '*') {
                $from = 0;
                $to = 60;
            } // handle range
            elseif (strpos($expr, '-') !== false) {
                $e = explode('-', $expr);
                if (sizeof($e) !== 2) {
                    throw new Exception('Invalid cron expression, expecting "from-to" structure: ' . $expr . '.');
                }
                $from = $this->getNumeric($e[0]);
                $to = $this->getNumeric($e[1]);
            } // handle regular token
            else {
                $from = $this->getNumeric($expr);
                $to = $from;
            }

            if ($from === false || $to === false) {
                throw new Exception('Invalid cron expression: ' . $expr);
            }

            return ($num >= $from) && ($num <= $to) && ($num % $mod === 0);
        }

        /**
         * Get a numeric version of linux cron dates.
         *
         * @param string $value
         * @return int|boolean
         */
        public function getNumeric($value)
        {
            if (is_numeric($value)) {
                return $value;
            }

            if (is_string($value)) {
                $value = strtolower(substr($value, 0, 3));
                if (array_key_exists($value, self::$_numerics)) {
                    return self::$_numerics[$value];
                }
            }

            return false;
        }

    }
