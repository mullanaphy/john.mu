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

    use PHY\Event;
    use PHY\Event\Item as EventItem;

    /**
     * Mailer namespace
     *
     * @package PHY\Component\Mailer
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Mailer extends AComponent
    {

        /**
         * {@inheritDoc}
         */
        public function get($key, $graceful = false)
        {
            $namespace = $this->getNamespace();
            if (strpos($key, '.') !== false) {
                $callMethods = explode('.', $key);
                $key = array_shift($callMethods);
            } else {
                $callMethods = [];
            }
            $values = explode('/', $key);
            if ($values) {
                $value = array_shift($values);
            } else {
                $value = $this->getApp()->get('core/component/mailer');
            }
            if (!array_key_exists($namespace, $this->resources)) {
                $this->resources[$namespace] = [];
            }
            if (!array_key_exists($value, $this->resources[$namespace])) {
                $mailer = false;
                $config = $this->getApp()->get('config/mailer/' . $value);
                $event = new EventItem('component/load/before', [
                    'config' => $config,
                    'type' => $value
                ]);
                Event::dispatch($event);
                if ($event->config && array_key_exists('type', $event->config)) {
                    $mailer = $this->getClass('Mailer\\' . ucfirst($event->config['type']));
                    $mailer = new $mailer($event->config);
                }
                if ($mailer) {
                    $this->resources[$namespace][$value] = $mailer;
                    Event::dispatch(new EventItem('component/mailer/load/after', [
                        'object' => $mailer,
                        'type' => $value
                    ]));
                } else {
                    if (!$graceful) {
                        throw new Exception('Component "mailer/' . $value . '" is undefined.');
                    }
                }
            }
            if ($callMethods) {
                $temp = $this->resources[$namespace][$value];
                foreach ($callMethods as $callMethod) {
                    if (strpos(':', $callMethod)) {
                        $parameters = explode(':', $callMethod);
                        $callMethod = array_shift($parameters);
                    } else {
                        $parameters = [];
                    }
                    if (!method_exists($temp, $callMethod)) {
                        $method = false;
                        foreach (['get', 'has', 'is'] as $m) {
                            if (method_exists($temp, $m . $callMethod)) {
                                $method = $m . $callMethod;
                                break;
                            }
                        }
                        if (!$method) {
                            throw new Exception('Magically (boo) loaded method broke the chain due to being lame... ' . $callMethod . ' is lame...');
                        }
                        $callMethod = $method;
                    }
                    $temp = call_user_func_array([$temp, $callMethod], $parameters);
                }
                return $temp;
            } else {
                return $this->resources[$namespace][$value];
            }
        }

    }
