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

    namespace PHY\View;

    /**
     * Create a pagination block.
     *
     * @package PHY\View\Pagination
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Pagination extends AView
    {

        /**
         * Generate the block depending on these variables:
         *     'limit' => number of items to show
         *     'total' => total number of items
         *     'pageId' => current page id
         *     'attributes' => attributes to add to the url links
         *     'url' => {
         *         if 'Function' => 'Function'($i) { } will be called for each $i
         *             return a string/array
         *         if 'Array' => 'Array'['pageId'] = $i will be called for each $i
         *         if 'String' => {
         *             if 'QueryString' => appends a pageId=$i to QueryString
         *             if '[%i]' => replaces [%i] with $i
         *             else => appends ?pageId=$i
         */
        public function structure()
        {
            $limit = (int)$this->getVariable('limit', 25);
            $total = (int)$this->getVariable('total', 0);
            $pages = ceil($total / $limit);
            if ($pages > 1) {
                $id = (int)$this->getVariable('pageId', 1);
                if ($id < 1 || $id > $pages) {
                    $id = 1;
                }
                $tag = $this->tag();
                $pagination = $tag->ul;
                $pagination->class('pagination');
                $url = $this->getVariable('url', null);
                switch (gettype($url)) {
                    case 'object':
                        if (get_class($url) === 'Closure') {
                            break;
                        } else {
                            $url = (array)$url;
                        }
                    case 'array':
                        $_url = $url;
                        $url = function ($i) use ($_url) {
                            $_url['pageId'] = $i;
                            return $_url;
                        };
                        break;
                    case 'string':
                        if (strpos($url, '?') !== false) {
                            $_url = explode('?', $url);
                            parse_str($_url[1], $parameters);
                            $url = function ($i) use ($_url, $parameters) {
                                $parameters['pageId'] = $i;
                                return $_url . '?' . http_build_query($parameters);
                            };
                        } elseif (strpos($url, '[%i]') !== false) {
                            $_url = $url;
                            $url = function ($i) use ($_url) {
                                return str_replace('[%i]', $i, $_url);
                            };
                        } else {
                            $_url = $url;
                            $url = function ($i) use ($_url) {
                                return $_url . '?pageId=' . $i;
                            };
                        }
                        break;
                    default:
                        $url = function ($i) {
                            return $i;
                        };
                }
                $attributes = $this->getVariable('attributes', []);
                $start = 1;
                $end = $pages;
                $helper = $tag->helper();
                if ($start > 1) {
                    $pagination->append($tag->li($helper->url('&laquo;', $url($start - 1), $attributes)));
                }
                for ($i = $start; $i <= $end; ++$i) {
                    $page = $helper->url($i, $url($i), $attributes);
                    $li = $tag->li($page);
                    if ($i === $id) {
                        $li->class('active');
                    }
                    $pagination->append($li);
                }
                if ($end < $pages) {
                    $pagination->append($tag->li($helper->url('&raquo;', $url($end + 1), $attributes)));
                }
                $this->setTemplate('pagination.phtml')->setVariable('pagination', $pagination);
            }
        }

    }
