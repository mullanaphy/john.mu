<?php

    /**
     * jo.mu
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to john@jo.mu so we can send you a copy immediately.
     */

    namespace PHY\Controller;

    use PHY\App;
    use PHY\Component\Mailer;
    use PHY\Http\Exception\Forbidden;
    use PHY\Http\Exception\ServerError;
    use PHY\Http\Response\Json as JsonResponse;
    use PHY\Http\Response\Xml as XmlResponse;
    use PHY\Model\Authorize;
    use PHY\Model\Blog;
    use PHY\Model\Blog\Relation as BlogRelation;
    use PHY\Model\Config as ConfigModel;
    use PHY\Model\Message;
    use PHY\Model\User;
    use Michelf\Markdown;
    use PHY\Variable\Str;
    use PHY\View\Block;

    /**
     * My admin panel. Theoretically this should probably be broken up into smaller controllers but #yolo.
     *
     * @package PHY\Controller\Admin
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (http://jo.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Admin extends AController
    {

        /**
         * {@inheritDoc}
         */
        public function __construct(App $app = null)
        {
            parent::__construct($app);
            /** @var \PHY\Model\IUser $user */
            $user = $app->getUser();

            if (!$user->exists()) {
                $app->set('session/_redirect', '/admin');
                $this->redirect('/login');
            } else {
                /** @var \PHY\Database\IManager $manager */
                $manager = $app->get('database')->getManager();
                $authorize = Authorize::loadByRequest('controller/admin', $manager);
                if (!$authorize->exists()) {
                    $authorize->request = 'controller/admin';
                    $authorize->allow = 'user admin super-admin';
                    $authorize->deny = 'all';
                    $manager->save($authorize);
                }
                if (!$authorize->isAllowed($user)) {
                    throw new Forbidden('Sorry, not allowed in the admin section.');
                }
            }
        }

        /**
         * {@inheritDoc}
         */
        public function action($action = 'index')
        {
            $layout = $this->getLayout();
            $page = $layout->block('layout');
            $page->setTemplate('admin/layout-2col.phtml');
            $head = $layout->block('head');
            $files = $head->getVariable('files');
            $files['css'][1] = 'foundation.min.css';
            $head->setVariable('files', $files);
            $page->setChild('header', ['template' => 'admin/header.phtml']);
            $layout->block('modal')->setTemplate('admin/modal.phtml');
            $layout->buildBlocks('breadcrumb', [
                'template' => 'admin/' . ($action !== 'index'
                        ? $action . '/'
                        : '') . 'breadcrumb.phtml'
            ]);
            $layout->block('layout')->setChild('breadcrumb', null);

            parent::action($action);
        }

        /**
         * {@inheritDoc}
         */
        public function index_get()
        {
            $app = $this->getApp();
            $content = $this->getLayout()->block('content');

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();
            $email = $manager->load(['key' => 'email'], new ConfigModel);
            $content->setVariable('email', $email->value);
            $content->setVariable('user', $app->getUser());

            $collection = $manager->getCollection('Message');
            $collection->limit(3);
            $collection->where()->field('read')->is('0000-00-00 00:00:00');
            $collection->order()->by('created')->direction('desc');
            $content->setVariable('messages', $collection);

            $collection = $manager->getCollection('Blog');
            $collection->order()->by('created')->direction('desc');
            $collection->limit(3);
            $content->setVariable('blog', $collection);
        }

        /**
         * GET /admin/authorize
         */
        public function authorize_get()
        {
            $app = $this->getApp();
            $request = $this->getRequest();
            $id = $request->get('id', false);
            $layout = $this->getLayout();
            $content = $layout->block('content');

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            if ($id !== false) {
                if ($id) {
                    $item = $manager->load($id, new Authorize);
                } else {
                    $item = new Authorize($request->get('authorize', []));
                }
                $content->setTemplate('admin/authorize/item.phtml');
                $content->setVariable('item', $item);
                $breadcrumb = $layout->block('breadcrumb');
                $breadcrumb->setVariable('item', $item);
            } else {
                $pageId = (int)$request->get('pageId', 1);
                if (!$pageId) {
                    $pageId = 1;
                }
                $limit = (int)$request->get('limit', 20);
                if (!$limit) {
                    $limit = 20;
                }

                $collection = $manager->getCollection('Authorize');
                $collection->limit((($pageId * $limit) - $limit), $limit);
                $collection->where()->field('deleted')->is(false);
                $collection->order()->by('request');

                $content->setTemplate('admin/authorize/collection.phtml');
                $content->setVariable('collection', $collection);
                $content->setChild('pagination', [
                    'viewClass' => 'pagination',
                    'pageId' => $pageId,
                    'limit' => $limit,
                    'total' => $collection->count(),
                    'url' => [
                        $this->url('admin/authorize'),
                        'limit' => $limit
                    ]
                ]);
            }
            if ($message = $app->get('session/admin/authorize/message')) {
                $app->delete('session/admin/authorize/message');
                $message['template'] = 'generic/message.phtml';
                $content->setChild('message', $message);
            }
        }

        /**
         * POST /admin/authorize
         */
        public function authorize_post()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            $data = $request->get('authorize', [
                'request' => '',
                'deny' => '',
                'allow' => ''
            ]);
            if ($id) {
                $item = $manager->load($id, new Authorize);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('authorize', [
                        'title' => 'Hmmm',
                        'type' => 'warning',
                        'message' => 'No ACL found for id: ' . $id
                    ]);
                }
            } else {
                $item = new Authorize($data);
            }
            $data['allow'] = str_replace([PHP_EOL, "\n", "\r"], ' ', $data['allow']);
            $data['allow'] = trim(preg_replace('#\s+#', ' ', $data['allow']));
            $data['deny'] = str_replace([PHP_EOL, "\n", "\r"], ' ', $data['deny']);
            $data['deny'] = trim(preg_replace('#\s+#', ' ', $data['deny']));
            $item->set($data);
            $manager->save($item);
            return $this->renderResponse('authorize', [
                'title' => 'Yeah boy!',
                'type' => 'success',
                'message' => 'Successfully updated: ' . $item->request
            ]);
        }

        /**
         * PUT /admin/authorize
         */
        public function authorize_put()
        {
            $this->authorize_post();
        }

        /**
         * DELETE /admin/authorize/id/{id}
         */
        public function authorize_delete()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            if ($id) {
                $item = $manager->load($id, new Authorize);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('authorize', [
                        'title' => 'Oh man...',
                        'type' => 'warning',
                        'message' => 'No ACL found for id: ' . $id
                    ]);
                }
            } else {
                return $this->renderResponse('authorize', [
                    'title' => 'Well?',
                    'type' => 'warning',
                    'message' => 'No ACL id provided.'
                ]);
            }
            $requestName = $item->request;
            $manager->delete($item);
            return $this->renderResponse('authorize', [
                'title' => 'Ok.',
                'type' => 'success',
                'message' => 'Successfully removed: ' . $requestName
            ]);
        }

        /**
         * GET /admin/user
         */
        public function user_get()
        {
            $app = $this->getApp();
            $request = $this->getRequest();
            $id = $request->get('id', false);
            $layout = $this->getLayout();
            $content = $layout->block('content');

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            if ($id !== false) {
                if ($id) {
                    $item = $manager->load($id, new User);
                } else {
                    $item = new User($request->get('user', []));
                }
                $content->setTemplate('admin/user/item.phtml');
                $content->setVariable('item', $item);
                $breadcrumb = $layout->block('breadcrumb');
                $breadcrumb->setVariable('item', $item);
            } else {
                $pageId = (int)$request->get('pageId', 1);
                if (!$pageId) {
                    $pageId = 1;
                }
                $limit = (int)$request->get('limit', 20);
                if (!$limit) {
                    $limit = 20;
                }

                $collection = $manager->getCollection('User');
                $collection->limit((($pageId * $limit) - $limit), $limit);
                $collection->where()->field('deleted')->is(false);
                $collection->order()->by('name');

                $content->setTemplate('admin/user/collection.phtml');
                $content->setVariable('collection', $collection);
                $content->setChild('pagination', [
                    'viewClass' => 'pagination',
                    'pageId' => $pageId,
                    'limit' => $limit,
                    'total' => $collection->count(),
                    'url' => [
                        $this->url('admin/user'),
                        'limit' => $limit
                    ]
                ]);
            }
            if ($message = $app->get('session/admin/user/message')) {
                $app->delete('session/admin/user/message');
                $message['template'] = 'generic/message.phtml';
                $content->setChild('message', $message);
            }
        }

        /**
         * POST /admin/user
         */
        public function user_post()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            $data = $request->get('user', [
                'group' => '',
                'username' => '',
                'name' => '',
                'title' => '',
                'bio' => '',
                'phone' => '',
                'email' => ''
            ]);
            try {
                $datetime = date('Y-m-d H:i:s');
                if ($id) {
                    $item = $manager->load($id, new User);
                    if (!$item->exists() || $item->deleted) {
                        $app->set('session/admin/user/message', [
                            'title' => 'Seriously?',
                            'type' => 'warning',
                            'message' => 'No user found for id: ' . $id
                        ]);
                        return $this->redirect('/admin/user');
                    }
                    $data['updated'] = $datetime;
                } else {
                    $item = new User($data);
                    if (!$data['password']) {
                        throw new \InvalidArgumentException('You must provide a password for new users.');
                    } else if ($data['password'] !== $data['confirm']) {
                        throw new \InvalidArgumentException('The passwords entered did not match.');
                    }
                    $data['updated'] = $datetime;
                    $data['created'] = $datetime;
                    $data['activity'] = $datetime;
                }
                if (!$data['email']) {
                    throw new \InvalidArgumentException('You must provide an email.');
                } else if (!$data['email']) {
                    throw new \InvalidArgumentException('You must provide a username.');
                }
                $data['group'] = 'user';
                if ($data['password'] && $data['password'] === $data['confirm']) {
                    $password = $data['password'];
                } else {
                    $password = false;
                }
                unset($data['password'], $data['confirm']);
                $item->set($data);
                if ($password) {
                    $item->set('password', $password);
                }
                $manager->save($item);
                return $this->renderResponse('user', [
                    'title' => 'Great Success!',
                    'type' => 'success',
                    'message' => 'Successfully updated: ' . $item->name
                ]);
            } catch (\Exception $e) {
                return $this->renderResponse('user', [
                    'title' => 'Slight error.',
                    'type' => 'warning',
                    'message' => $e->getMessage()
                ]);
            }
        }

        /**
         * PUT /admin/user
         */
        public function user_put()
        {
            $this->user_post();
        }

        /**
         * DELETE /admin/user/id/{id}
         */
        public function user_delete()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            if ($id) {
                $item = $manager->load($id, new User);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('user', [
                        'title' => 'Fiddlesticks...',
                        'type' => 'warning',
                        'message' => 'No user found for id: ' . $id
                    ]);
                }
            } else {
                return $this->renderResponse('user', [
                    'title' => 'Cannot be found.',
                    'type' => 'warning',
                    'message' => 'No user id provided.'
                ]);
            }
            $name = $item->name;
            $manager->delete($item);
            return $this->renderResponse('user', [
                'title' => 'Bye Bye!',
                'type' => 'success',
                'message' => 'Successfully removed: ' . $name
            ]);
        }

        /**
         * GET /admin/config
         */
        public function config_get()
        {
            $app = $this->getApp();
            $request = $this->getRequest();
            $id = $request->get('id', false);
            $layout = $this->getLayout();
            $content = $layout->block('content');

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            if ($id !== false) {
                if ($id) {
                    $item = $manager->load($id, new ConfigModel);
                } else {
                    $item = new ConfigModel($request->get('config', []));
                }
                $content->setTemplate('admin/config/item.phtml');
                $content->setVariable('item', $item);
                $breadcrumb = $layout->block('breadcrumb');
                $breadcrumb->setVariable('item', $item);
            } else {
                $pageId = (int)$request->get('pageId', 1);
                if (!$pageId) {
                    $pageId = 1;
                }
                $limit = (int)$request->get('limit', 20);
                if (!$limit) {
                    $limit = 20;
                }

                $collection = $manager->getCollection('Config');
                $collection->limit((($pageId * $limit) - $limit), $limit);
                $collection->order()->by('key');

                $content->setTemplate('admin/config/collection.phtml');
                $content->setVariable('collection', $collection);
                $content->setChild('pagination', [
                    'viewClass' => 'pagination',
                    'pageId' => $pageId,
                    'limit' => $limit,
                    'total' => $collection->count(),
                    'url' => [
                        $this->url('admin/config'),
                        'limit' => $limit
                    ]
                ]);
            }
            if ($message = $app->get('session/admin/config/message')) {
                $app->delete('session/admin/config/message');
                $message['template'] = 'generic/message.phtml';
                $content->setChild('message', $message);
            }
        }

        /**
         * POST /admin/config
         */
        public function config_post()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            $data = $request->get('config', [
                'key' => '',
                'value' => '',
                'type' => 'variable',
            ]);
            if ($id) {
                $item = $manager->load($id, new ConfigModel);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('config', [
                        'title' => 'Not Configured!',
                        'type' => 'warning',
                        'message' => 'No config found for id: ' . $id
                    ]);
                }
            } else {
                $item = new ConfigModel($data);
            }

            $item->set($data);
            $manager->save($item);

            return $this->renderResponse('config', [
                'title' => 'Configured!',
                'type' => 'success',
                'message' => 'Successfully updated: ' . $item->key
            ]);
        }

        /**
         * PUT /admin/config
         */
        public function config_put()
        {
            $this->config_post();
        }

        /**
         * DELETE /admin/config/id/{id}
         */
        public function config_delete()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            if ($id) {
                $item = $manager->load($id, new ConfigModel);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('config', [
                        'title' => 'Wasn\'t me...',
                        'type' => 'warning',
                        'message' => 'No config found for id: ' . $id
                    ]);
                } else if (in_array($item->key, ['address', 'email', 'phone'])) {
                    return $this->renderResponse('config', [
                        'title' => 'Denied!',
                        'type' => 'warning',
                        'message' => 'Sorry, you cannot delete the config for ' . $item->key . ' since that\'s used in a lot of places.'
                    ]);
                }
            } else {
                return $this->renderResponse('config', [
                    'title' => 'Not gonna do it.',
                    'type' => 'warning',
                    'message' => 'No config id provided.'
                ]);
            }
            $key = $item->key;
            $manager->delete($item);
            return $this->renderResponse('config', [
                'title' => 'Deconfigured!',
                'type' => 'success',
                'message' => 'Successfully removed: ' . $key
            ]);
        }

        /**
         * GET /admin/message
         */
        public function message_get()
        {
            $app = $this->getApp();
            $request = $this->getRequest();
            $id = $request->get('id', false);
            $layout = $this->getLayout();
            $content = $layout->block('content');

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            if ($id !== false) {
                if ($id) {
                    $item = $manager->load($id, new Message);
                } else {
                    $item = new Message;
                }
                if (!$item->exists()) {
                    return $this->renderResponse('message', [
                        'title' => 'This message doesn\'t exists',
                        'type' => 'warning',
                        'message' => 'No message found for id: ' . $id
                    ]);
                }

                if ($item->read === '0000-00-00 00:00:00') {
                    $read = date('Y-m-d H:i:s');
                    $item->set('read', $read);
                    $item->set('updated', $read);
                    $manager->save($item);
                }

                $content->setTemplate('admin/message/item.phtml');
                $content->setVariable('item', $item);
                $breadcrumb = $layout->block('breadcrumb');
                $breadcrumb->setVariable('item', $item);
            } else {
                $pageId = (int)$request->get('pageId', 1);
                if (!$pageId) {
                    $pageId = 1;
                }

                $limit = (int)$request->get('limit', 20);
                if (!$limit) {
                    $limit = 20;
                }

                $collection = $manager->getCollection('Message');
                $collection->limit((($pageId * $limit) - $limit), $limit);
                $collection->order()->by('created')->direction('desc');

                $content->setTemplate('admin/message/collection.phtml');
                $content->setVariable('collection', $collection);
                $content->setChild('pagination', [
                    'viewClass' => 'pagination',
                    'pageId' => $pageId,
                    'limit' => $limit,
                    'total' => $collection->count(),
                    'url' => [
                        $this->url('admin/message'),
                        'limit' => $limit
                    ]
                ]);
            }
            if ($message = $app->get('session/admin/message/message')) {
                $app->delete('session/admin/message/message');
                $message['template'] = 'generic/message.phtml';
                $content->setChild('message', $message);
            }
        }

        /**
         * DELETE /admin/message/id/{id}
         */
        public function message_delete()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            if ($id) {
                $item = $manager->load($id, new Message);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('message', [
                        'title' => 'Not Closed.',
                        'type' => 'warning',
                        'message' => 'No message found for id: ' . $id
                    ]);
                }
            } else {
                return $this->renderResponse('message', [
                    'title' => 'You must work!',
                    'type' => 'warning',
                    'message' => 'No message id provided.'
                ]);
            }
            $date = $item->date;
            $manager->delete($item);
            return $this->renderResponse('message', [
                'title' => 'Reopened!',
                'type' => 'success',
                'message' => 'Successfully removed: ' . $date
            ]);
        }

        public function reply_post()
        {
            $app = $this->getApp();
            $request = $this->getRequest();
            $layout = $this->getLayout();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            try {
                $fields = $request->get('reply', []);

                $message = $manager->load(['id' => $fields['id']], new Message);
                if (!$message->exists()) {
                    throw new ServerError('Umm, you\'re trying to reply to a deleted message nimrod...');
                }

                /** @var $mail \PHY\Mailer\IMailer */
                $mail = $app->get('mailer');

                $mail->setTo([$fields['to']]);
                $mail->setFrom($fields['from']
                    ?: $app->getUser()->email);
                $mail->setSubject($fields['subject']);

                $block = new Block('html', ['fields' => $fields]);
                $block->setLayout($layout);
                $mail->setBody($block->setTemplate('contact/html.phtml')->toString(), 'html');
                $mail->setBody($block->setTemplate('contact/text.phtml')->toString(), 'text');

                $mail->send();

                $message->replied = date('Y-m-d H:i:s');
                $manager->save($message);

                return $this->renderResponse('reply', [
                    'title' => 'Fiddlesticks...',
                    'type' => 'success',
                    'message' => 'Good job, you\'ve replied to ' . $message->name,
                ]);

            } catch (\Exception $e) {
                return $this->renderResponse('reply', [
                    'title' => 'Fiddlesticks...',
                    'type' => 'warning',
                    'message' => $e->getMessage(),
                ]);
            }

        }

        /**
         * GET /admin/blog
         */
        public function blog_get()
        {
            $app = $this->getApp();
            $request = $this->getRequest();
            $id = $request->get('id', false);
            $layout = $this->getLayout();
            $content = $layout->block('content');

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            if ($id !== false) {
                if ($id) {
                    $item = $manager->load($id, new Blog);
                } else {
                    $item = new Blog($request->get('blog', []));
                }
                $content->setTemplate('admin/blog/item.phtml');
                $content->setVariable('item', $item);
                $breadcrumb = $layout->block('breadcrumb');
                $breadcrumb->setVariable('item', $item);
            } else {
                $pageId = (int)$request->get('pageId', 1);
                if (!$pageId) {
                    $pageId = 1;
                }
                $limit = (int)$request->get('limit', 20);
                if (!$limit) {
                    $limit = 20;
                }

                $collection = $manager->getCollection('Blog');
                $collection->limit((($pageId * $limit) - $limit), $limit);
                $collection->order()->by('updated')->direction('desc');

                $content->setTemplate('admin/blog/collection.phtml');
                $content->setVariable('collection', $collection);
                $content->setChild('pagination', [
                    'viewClass' => 'pagination',
                    'pageId' => $pageId,
                    'limit' => $limit,
                    'total' => $collection->count(),
                    'url' => [
                        $this->url('admin/blog'),
                        'limit' => $limit
                    ]
                ]);
            }
            if ($message = $app->get('session/admin/blog/message')) {
                $app->delete('session/admin/blog/message');
                $message['template'] = 'generic/message.phtml';
                $content->setChild('message', $message);
            }
        }

        /**
         * POST /admin/blog
         */
        public function blog_post()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            $data = $request->get('blog', [
                'date' => 0,
                'title' => '',
                'reason' => '',
            ]);
            if ($id) {
                $item = $manager->load($id, new Blog);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('blog', [
                        'title' => 'Lost.',
                        'type' => 'warning',
                        'message' => 'No article found for id: ' . $id
                    ]);
                }
            } else {
                $data['created'] = date('Y-m-d');
                $data['author_id'] = $app->getUser()->id();
                $item = new Blog($data);
            }
            $data['updated'] = date('Y-m-d');
            $item->set($data);
            $manager->save($item);

            /*
             * Lets render and cache our blog post so we don't have to do it on a page load.
             */
            $cache = $app->get('cache/rendered');
            $cache->flush();
            $post = Markdown::defaultTransform($item->content);
            $cache->set('blog/' . $item->id() . '/rendered', $post, 86400 * 31);
            $description = strip_tags(Markdown::defaultTransform((new Str(ucfirst($item->content)))->toShorten(160)));
            $cache->set('blog/' . $item->id() . '/description', $description, 86400 * 31);

            if (!$id) {
                $previous = $manager->load(['next' => ''], new BlogRelation);
                if ($previous->exists()) {
                    $previous->next = $item->slug;
                }
                $manager->save($previous);
                $new = new BlogRelation([
                    'slug' => $item->slug,
                    'previous' => $previous->slug,
                    'next' => ''
                ]);
                $manager->save($new);
            }

            return $this->renderResponse('blog', [
                'title' => 'Posted!',
                'type' => 'success',
                'message' => 'Successfully updated: ' . $item->title
            ]);
        }

        /**
         * PUT /admin/blog
         */
        public function blog_put()
        {
            $this->blog_post();
        }

        /**
         * DELETE /admin/blog/id/{id}
         */
        public function blog_delete()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');
            $manager = $database->getManager();

            $id = (int)$request->get('id', 0);
            if ($id) {
                $item = $manager->load($id, new Blog);
                if (!$item->exists() || $item->deleted) {
                    return $this->renderResponse('blog', [
                        'title' => 'Telephone.',
                        'type' => 'warning',
                        'message' => 'No article found for id: ' . $id
                    ]);
                }
            } else {
                return $this->renderResponse('blog', [
                    'title' => 'You must work!',
                    'type' => 'warning',
                    'message' => 'No article id provided.'
                ]);
            }
            $title = $item->title;
            $slug = $item->slug;
            $manager->delete($item);

            /** @var BlogRelation $relation */
            $relation = $manager->load(['slug' => $slug], new BlogRelation);

            /** @var BlogRelation $previous */
            if ($relation->previous !== '') {
                $previous = $manager->load(['slug' => $relation->previous], new BlogRelation);
            } else {
                $previous = new BlogRelation;
            }

            /** @var BlogRelation $next */
            if ($relation->next !== '') {
                $next = $manager->load(['slug' => $relation->next], new BlogRelation);
            } else {
                $next = new BlogRelation;
            }

            if ($next->exists() && $previous->exists()) {
                $previous->next = $next->slug;
                $next->previous = $previous->slug;
                $manager->save($next);
                $manager->save($previous);
            } else if ($next->exists()) {
                $next->previous = '';
                $manager->save($next);
            } else if ($previous->exists()) {
                $previous->next = '';
                $manager->save($previous);
            }

            $manager->delete($relation);
            if ($next) {
                $manager->save($next);
            }

            /*
             * We no longer need any rendered versions of our blog post.
             */
            $cache = $app->get('cache/rendered');
            $cachedVersions = $cache->get('html/index/blog');
            if ($cachedVersions) {
                foreach ($cachedVersions as $cached) {
                    $cache->delete($cached);
                    $cache->delete($cached . '-inner');
                    $cache->delete($cached . '-count');
                }
                $cache->delete('html/index/blog');
            }
            $cache->delete('sitemap');
            $cache->delete('blog/' . $item->id() . '/rendered');
            $cache->delete('blog/' . $item->id() . '/description');

            return $this->renderResponse('blog', [
                'title' => 'No longer relevant?!',
                'type' => 'success',
                'message' => 'Successfully removed: ' . $title
            ]);
        }

        /**
         * GET /admin/relations
         */
        public function relations_get()
        {
            $app = $this->getApp();
            $request = $this->getRequest();

            /**
             * @var \PHY\Database\IDatabase $database
             */
            $database = $app->get('database');

            /** @var \PHY\Database\IManager $manager */
            $manager = $database->getManager();

            /*
             * First let's delete EVERYTHING from blog_relation
             */
            $manager->dropTable(new BlogRelation);

            $collection = $manager->getCollection('Blog');
            $collection->order()->by('created')->direction('asc');

            $previous = '';
            $current = null;
            foreach ($collection as $item) {
                if ($current !== null) {
                    $current->next = $item->slug;
                    $manager->save($current);
                }
                $current = new BlogRelation([
                    'slug' => $item->slug
                ]);
                $current->previous = $previous;
                $previous = $item->slug;
            }
            if ($current !== null) {
                $current->next = '';
                $manager->save($current);
            }

            return $this->renderResponse('index', [
                'title' => 'Aw Yiss',
                'type' => 'success',
                'message' => 'Successfully rebuilt relations',
            ]);
        }

        private function renderResponse($action, $message)
        {
            $accept = $this->getRequest()->getHeader('Accept', 'text/html;');
            if ($accept) {
                $accept = explode(',', $accept);
                foreach ($accept as $type) {
                    switch (trim($type)) {
                        case 'text/html':
                            $app = $this->getApp();
                            $app->set('session/admin/' . $action . '/message', $message);
                            $this->getResponse()->setStatusCode($message['type'] === 'success'
                                ? 200
                                : 500);
                            return $this->redirect('/admin/' . $action);
                            break;
                        case 'application/json':
                        case 'text/json':
                        case 'text/javascript':
                            $response = new JsonResponse;
                            $response->setData($message);
                            $response->setStatusCode($message['type'] === 'success'
                                ? 200
                                : 500);
                            $this->setResponse($response);
                            return $response;
                            break;
                        case 'application/xml':
                        case 'text/xml':
                            $response = new XmlResponse;
                            $response->setData($message);
                            $response->setStatusCode($message['type'] === 'success'
                                ? 200
                                : 500);
                            $this->setResponse($response);
                            return $response;
                            break;
                        default:
                    }
                }
            }
            $app = $this->getApp();
            $app->set('session/admin/' . $action . '/message', $message);
            $this->getResponse()->setStatusCode($message['type'] === 'success'
                ? 200
                : 500);
            return $this->redirect('/admin/' . $action);
        }

    }
