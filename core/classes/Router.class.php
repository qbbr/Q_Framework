<?php
/**
 * Router
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Router
{

    /**
     * разбор запроса
     *
     * @static
     * @access public
     * @param string $uri REQUEST_URI
     * @return mixed
     */
    static public function parseRequest($uri)
    {
        require_once CONFIGS . DS . 'urlpatterns.php';

        $a = Q_Registry::get('urlPatterns');

        foreach ($a as $pattern) {

            $requestMethod = $pattern[0];
            $regex = '#' . $pattern[1] . '#';
            $controller = $pattern[2];
            $method = $pattern[3];

            if ($requestMethod != '*' && $requestMethod != $_SERVER['REQUEST_METHOD']) {
                continue;
            }

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
 
                $request = new Q_Request($matches);

                $isAdmin = substr($regex, 1, 5) == '^/cp/';
                
                $isAuth = Q_Authorization::checkBySession();
                $isStaff = Q_Registry::get('user', 'is_staff');
                
                $access = ($isAdmin && $isAuth && $isStaff)
                        ? 'admin'
                        : 'client';

                if ($isAdmin && !$isAuth && $controller != 'Cp') {
                    $controller = 'Cp';
                    $method = 'index';
                }

                $path = APPS . DS . $access . DS . $controller . '.' . $access . 'Controller.php';
                
                $controller = 'Q_' . $controller;
                
                if (!is_file($path)) {
                    self::error(404);
                }

                include_once $path;

                $method .= 'Action';

                if (!@is_callable(array($controller, $method))) {
                    self::error(404);
                }

                $m = new $controller($controller);
                return $m->$method($request);

                break;
            }
            
        }

        return self::error(404);
    }


    static public function error($en)
    {
        echo 'ERROR' . $en;
        exit();
    }

}
