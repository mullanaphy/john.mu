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

/**
 * Home page.
 *
 * @package PHY\Controller\Index
 * @category PHY\JO
 * @copyright Copyright (c) 2014 John Mullanaphy (https://john.mu/)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author John Mullanaphy <hi@john.mu>
 */
class Turkey extends AController
{
    const TURKEY_TEMPLATE = <<<'TURKEY_DAY'
                    ___                       
                   ' _<_\                     
                  /'/o)o)                     
                  \ \_/\/  ,-----------------.
        ,.-=--.-. |.(____\ |  0XXXXXXXXXXXX  |
      ;:_  \  |  `:.`---'  |  1XXXXXXXXXXXX  |
   ,-'  `.  ' \    \\ / |  |  2XXXXXXXXXXXX  |
 .'  -.   `_      __'|/ '| |  3XXXXXXXXXXXX  |
,'     `.     ,,-'  | |  | |  4XXXXXXXXXXXX  |
|'` .__   ,--'      | '`'\ `-----------------'
`._    _/'        ,/      |        ||_        
|  `...'                  |--....,-'__\.__    
`     |                 ,' \      -||  -:;    
 |   |                `-'  ;       ||_;--"    
 `.  |         |           |     ;-''         
  `.  \___    /           /-._;,'             
    ;,-'  ,  /           /                    
   /   ,-'_ / _'  |    ,'                     
  |  ,' ,' / :     \_,,'                      
  | .'_' ,' ||      '.                        
  '/',;-' _ /\    --.` ..___                  
    '      ; .`--.   `\ _>.                   
         . ,' `\'.     \\'''                  
          '     -'      '                     
TURKEY_DAY;


    /**
     * GET /turkey
     */
    public function index_get()
    {
        $response = $this->getResponse();
        $request = $this->getRequest();
        $acceptType = $request->getHeader('Accept');

        $lines = [
            '',
            '',
            'Ok!',
            '',
            '',
        ];

        if (strpos($acceptType, 'text/poultry') === false) {
            $status = 415;
            $lines[2] = 'Unsupported';
            $lines[3] = 'Media Type!';
            $lines[4] = $acceptType;
        } else {
            $contentType = $request->getHeader('Content-Type');
            $requestBody = file_get_contents('php://input');

            if ($contentType === 'text/poultry') {
               if (self::isValidTurkey($requestBody)) {
                   $status = 201;
                   $lines[2] = 'Turkey';
                   $lines[3] = 'Received!'; 
               } else {
                   $status = 400;
                   $lines[2] = 'Malformed';
                   $lines[3] = 'Turkey!';
               }
            } else {
                if (!strlen($requestBody)) {
                    $status = 400;
                    $lines[2] = 'Missing';
                    $lines[3] = 'Request';
                    $lines[4] = 'Body!';
                } else if (strlen($requestBody) > 39) {
                    $status = 413;
                    $lines[2] = 'Content';
                    $lines[3] = 'Too Large';
                    $lines[4] = 'Limit 39lbs!';
                } else {
                    $status = 200;
                    $lines[2] = substr($requestBody, 0, 13);
                    $lines[3] = substr($requestBody, 13, 26);
                    $lines[4] = substr($requestBody, 26, 39);
                }
            }
        }

        $lines[0] = $status;
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', 'text/poultry; charset=utf-8');
        $response->setContent([
            preg_replace_callback('/(\d)X{12}/', function($digit) use($lines) { return str_pad(substr(trim($lines[$digit[1]]), 0, 13), 13); }, self::TURKEY_TEMPLATE),
            PHP_EOL,
        ]);
        return $response;
    }

    private function isValidTurkey($turkey) {
        $newTurkey = substr_replace($turkey, '0XXXXXXXXXXXX', 218, 13);
        $newTurkey = substr_replace($newTurkey, '1XXXXXXXXXXXX', 265, 13);
        $newTurkey = substr_replace($newTurkey, '2XXXXXXXXXXXX', 312, 13);
        $newTurkey = substr_replace($newTurkey, '3XXXXXXXXXXXX', 359, 13);
        $newTurkey = substr_replace($newTurkey, '4XXXXXXXXXXXX', 406, 13);

        return trim($newTurkey) === trim(self::TURKEY_TEMPLATE);
    }
}

