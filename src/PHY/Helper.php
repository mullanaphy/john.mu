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
     * to license@phyneapple.com so we can send you a copy immediately.
     *
     */

    namespace PHY;

    use Highlight\Highlighter;
    use Michelf\MarkdownExtra as Markdown;
    use PHY\Variable\Str;

    /**
     * Blog page.
     *
     * @package PHY\Helper
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Helper
    {
        private static $markdown = null;
        private static $highlighter = null;

        /**
         * Render Markdown into HTML.
         *
         * @param string $source
         * @return string
         */
        public static function renderMarkdown($source)
        {
            return self::getMarkdown()->transform($source);
        }

        /**
         * Get our Highlighter singleton.
         *
         * @return Highlighter
         */
        public static function getHighlighter()
        {
            if (self::$highlighter === null) {
                self::$highlighter = new Highlighter;
            }
            return self::$highlighter;
        }

        /**
         * Get our Markdown singleton.
         *
         * @return Markdown
         */
        public static function getMarkdown()
        {
            if (self::$markdown === null) {
                self::$markdown = new Markdown;
                self::$markdown->code_block_content_func = function ($code, $language) {
                    try {
                        $highlighted = self::getHighlighter()->highlight($language, $code);
                        return $highlighted->value;
                    } catch (\Exception $exception) {
                    }
                    return htmlspecialchars($code);
                };
            }
            return self::$markdown;
        }

        /**
         * Get the rendered data for a blog post, cache it if needed.
         *
         * @param \PHY\Model\Blog $item
         * @param \PHY\Cache\ICache $cache
         * @return \stdClass
         */
        public static function getRenderedBlogPost($item, $cache)
        {
            if (!$description = $cache->get('blog/' . $item->id() . '/description')) {
                $description = strip_tags(self::renderMarkdown((new Str(ucfirst($item->content)))->toShorten(256)));
                $cache->set('blog/' . $item->id() . '/description', $description, 86400 * 31);
            }
            if (!$post = $cache->get('blog/' . $item->id() . '/rendered')) {
                $post = self::renderMarkdown($item->content);
                $cache->set('blog/' . $item->id() . '/rendered', $post, 86400 * 31);
            }
            $cachedData = new \stdClass;
            $cachedData->description = $description;
            $cachedData->post = $post;
            return $cachedData;
        }
    }
