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

    namespace PHY\Component;

    use PHY\Cache\Cookie as CacheCookie;
    use PHY\Cache\ICache;
    use PHY\Event;
    use PHY\Event\Item as EventItem;

    /**
     * Cache namespace
     *
     * @package PHY\Component\Cache
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Cookie extends AComponent
    {

        /**
         * {@inheritDoc}
         */
        public function get($key)
        {
            $namespace = $this->getNamespace();
            $app = $this->getApp();
            $values = explode('/', $key);
            if ($values) {
                $value = array_shift($values);
            } else {
                $value = $app->get('core/component/cookie');
            }
            if (!array_key_exists($namespace, $this->resources)) {
                $this->resources[$namespace] = [];
            }
            if (!array_key_exists($value, $this->resources[$namespace])) {
                $cache = false;
                try {
                    $config = $app->get('config/cookie');
                } catch (\Exception $e) {
                    $config = ['prefix' => 'PHY_'];
                }
                if ($config) {
                    $event = new EventItem('component/cookie/load/before', [
                        'config' => $config,
                        'type' => $value
                    ]);
                    Event::dispatch($event);
                    $config = $event->config
                        ?: [];
                    if ($config) {
                        try {
                            $cache = $app->getClass('Cache\\Cookie');
                            if (!$cache) {
                                throw new Exception('No cookie cache model found');
                            }
                            $cache = new $cache($config);
                            if (!($cache instanceof ICache)) {
                                throw new Exception('Cookie cache object must use \PHY\Cache\ICache.');
                            }
                        } catch (\Exception $e) {
                            $cache = new CacheCookie;
                        }
                    }
                } else {
                    try {
                        $config = ['prefix' => 'PHY_'];
                        $event = new EventItem('component/cookie/load/before', [
                            'config' => $config,
                            'type' => $value
                        ]);
                        Event::dispatch($event);
                        $config = $event->config;
                        $cache = new CacheCookie($config);
                    } catch (\Exception $e) {
                        $cache = new CacheCookie;
                    }
                }
                if ($cache) {
                    $this->resources[$namespace][$value] = $cache;
                    Event::dispatch(new EventItem('component/cookie/load/after', [
                        'object' => $cache,
                        'type' => $value
                    ]));
                } else {
                    throw new Exception('Component "cookie/' . $value . '" is undefined.');
                }
            }
            return $this->resources[$namespace][$value];
        }

    }
