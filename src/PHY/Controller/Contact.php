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

    namespace PHY\Controller;

    use PHY\Http\Exception\ServerError;
    use PHY\Model\Message;
    use PHY\Model\Config as ConfigModel;

    /**
     * Contact page.
     *
     * @package PHY\Controller\Contact
     * @category PHY\JO
     * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <hi@john.mu>
     */
    class Contact extends AController
    {

        /**
         * GET /contact
         */
        public function index_get()
        {
            $layout = $this->getLayout();
            $head = $layout->block('head');
            $head->setVariable('title', 'Contact');
            $head->setVariable('description', 'Send messages my way. Hopefully not hate mail but if it must be so...');
        }

        /**
         * POST /contact
         */
        public function index_post()
        {
            $layout = $this->getLayout();
            $created = date('Y-m-d H:i:s');
            $fields = $this->getRequest()->get('email', [
                'name' => '',
                'email' => '',
                'content' => '',
                'created' => $created,
            ]);
            $success = false;
            $error = 'Something seems to have gone astray.';
            try {

                $app = $this->getLayout()->getController()->getApp();
                /**
                 * @var \PHY\Database\IDatabase $database
                 */
                $database = $app->get('database');
                $manager = $database->getManager();

                if (!array_key_exists('name', $fields) || !$fields['name']) {
                    throw new ServerError('Yo bro! You never entered your name, how am I supposed to know who this is?');
                }
                if (!array_key_exists('email', $fields) || !$fields['email']) {
                    throw new ServerError('Well it\'s kind of hard to reply to you without an email address...');
                }
                if (!array_key_exists('content', $fields) || !$fields['content']) {
                    throw new ServerError('Thanks? No message but okay...');
                }
                if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new ServerError('Yeah... That looks like a real email address... If it is then I apologize for the sarcasm and feel free to belittle me on twitter.');
                }

                $message = new Message([
                    'name' => $fields['name'],
                    'email' => $fields['email'],
                    'content' => $fields['content'],
                    'created' => $created,
                    'updated' => $created,
                ]);
                $manager->save($message);

                $success = true;
            } catch (\Exception $e) {
                $success = false;
                $error = $e->getMessage();
            }
            if ($success) {
                $this->index_get();
                $content = $layout->block('content');
                $content->setChild('contact/message', [
                    'template' => 'generic/message/success.phtml',
                    'message' => 'Thank you for your submission, I should get back to you within 24 hours.',
                ]);
                foreach ($fields as $field => $value) {
                    $content->setVariable($field, $value);
                }
            } else {
                $content = $layout->block('content');
                $content->setVariable('fields', $fields);
                $content->setChild('contact/message', [
                    'template' => 'generic/message/error.phtml',
                    'message' => $error,
                ]);
            }
        }

    }
