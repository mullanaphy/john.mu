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

    namespace PHY;

    /*
     * Define these default $_SERVER values if this is being run from the command line.
     */
    if (array_key_exists('PWD', $_SERVER)) {
        $_SERVER['DOCUMENT_ROOT'] = $_SERVER['PWD'];
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        define('PLAIN_TEXT', true);
    } else {
        define('PLAIN_TEXT', false);
    }

    /*
     * Initiates Phyneapple's core files.
     */
    require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

    /**
     * This can be called via command line or browser. Runs Cron tasks that
     * match the time expressions.
     *
     * @package PHY
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    call_user_func(function () {
        /*
         * Make sure we're on PHP5.4+
         */
        if (version_compare(phpversion(), '5.4.0', '<') === true) {
            echo '<html><head><title>Fiddlesticks...</title></head><body><div><h3>Sorry Mate!</h3><p>PHY 2.0 supports PHP 5.4+.</p></div></body></html>';
            exit;
        }

        /*
         * Setup our app, add a debugger, and start profiling.
         */
        $app = new App;
        $debugger = new Debugger;
        $debugger->profile(true);
        $app->setDebugger($debugger);

        /*
         * Now add a path object for routing files.
         */
        $path = new Path([
            'base' => dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR
        ]);
        $app->setPath($path);

        /*
         * Setup our site and debugging.
         */
        $app->setNamespace(array_key_exists('PHY_NAMESPACE', $_SERVER)
            ? $_SERVER['PHY_NAMESPACE']
            : 'default');

        if (!($tasks = $app->get('cache/core/cron/tasks'))) {
            $tasks = [];
            $files = glob('config' . $app->getNamespace() . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . '*.json');
            foreach ($files as $file) {
                $FILE = fopen($file, 'r');
                if ($FILE) {
                    $content = fread($FILE, filesize($file));
                    $content = @json_decode($content);
                    if ($content) {
                        if (is_array($content)) {
                            $tasks = array_merge($tasks, $content);
                        } else {
                            $tasks[] = $content;
                        }
                    }
                }
            }
            $app->set('cache/core/cron/tasks', $tasks);
        }
        if ($tasks) {
            $Cron = new Cron;
            $Cron->setTasks($tasks);
            $run = 0;
            foreach ($Cron as $task) {
                $lock = Cron::lock($task->get('label'));
                if (!is_file($lock)) {
                    $LOCK = fopen($lock, 'w');
                    fwrite($LOCK, date('Y-m-d H:i:s'));
                    fclose($LOCK);
                    $task->run();
                    $run += (int)REST::success($task->response());
                    @unlink($lock);
                }
            }
            if (!PLAIN_TEXT) {
                echo '<!DOCTYPE html><html><head><title>Cron</title><style type="text/css">*{border:0;margin:0;padding:0;}body,html{background:#000;color:#72f90c;font-family:monaco,lucida console,courier new,monotype;font-size:13px;line-height:130%;padding:5px;}</style></head><body><pre>';
            } else {
                echo PHP_EOL;
            }
            $message = 'Ran ' . $run . ' out of a possible ' . count($Cron) . ' tasks';
            echo $message, PHP_EOL, '--------------------------------------------------', PHP_EOL, 'RUNTIME: ', $app->profile();
            if (!PLAIN_TEXT) {
                echo '</pre></body></html>';
            } else {
                echo PHP_EOL, PHP_EOL;
            }
        }
    });
