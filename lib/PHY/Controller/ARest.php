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

    namespace PHY\Controller;

    use PHY\Event;
    use PHY\Event\Item as EventItem;
    use PHY\Http\Response\Json as Response;

    /**
     * Boilerplate abstract class for Controllers.
     *
     * @package PHY\Controller
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    abstract class ARest extends AController implements IRest
    {

        /**
         * Lazy load our Response. If one doesn't exist, we'll create a globally
         * based Request.
         *
         * @return IRest
         */
        public function getResponse()
        {
            if ($this->request === null) {
                $event = new EventItem('controller/response/before', [
                    'controller' => $this
                ]);
                Event::dispatch($event);
                $this->request = new Response;
                Event::dispatch(new EventItem('controller/response/after', [
                    'controller' => $this,
                    'request' => $this->request
                ]));
            }
            return $this->request;
        }

        /**
         * {@inheritDoc}
         */
        public function success($message, $status = 200)
        {
            $response = new Response;
            $response->setStatus($status);
            $response->append($message);
            return $response;
        }

        /**
         * {@inheritDoc}
         */
        public function error($message, $status = 500)
        {
            $response = new Responset;
            $response->setStatus($status);
            $response->append($message);
            return $response;
        }

    }
