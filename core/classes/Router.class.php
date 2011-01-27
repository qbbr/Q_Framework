<?php
/**
 * Router
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */
class Q_Router
{
    /**
     * @staticvar ReflectionAnnotatedClass
     */
    static private $_reflection = null;
    
    /**
     * Разбор запроса
     *
     * @static
     * @access public
     * @param string $uri REQUEST_URI
     * @throws Q_MyException
     * @return mixed
     */
    static public function parseRequest($uri)
    {
        $a = require_once CONFIGS . DS . 'urlpatterns.php';

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
                
                //$isAuth = $isStaff = true;

                $access = ($isAdmin && $isAuth && $isStaff)
                        ? 'admin'
                        : 'client';

                if ($isAdmin && !$isAuth && $controller != 'Cp') {
                    $controller = 'Cp';
                    $method = 'index';
                }

                $path = APPS . DS . $access . DS . $controller . DS . $controller . '.' . $access . 'Controller.php';
                
                $moduleName = $controller;
                $controller = 'Q_' . $controller;
                
                if (!is_file($path)) {
                    self::error404();
                }  

                include_once $path;
                
                $method .= 'Action';

                $aclAction = self::getAclAction($controller, $method);
                
                $isAjaxRequest = self::isAjaxRequest();
                
                $isAjaxMethod = self::isAjaxMethod($controller, $method);
                
                if ($isAjaxRequest && !$isAjaxMethod) {
                    // @todo FIX
                    throw new Q_MyException('Метод ' . $method . ' не предназначен для AJAX запросов');
                }
                

                if (!@is_callable(array($controller, $method))) {
                    self::error404();
                }
                
                Q_Template::setAccess($access);
                $moduleViews = dirname($path) . DS . 'views' . DS;
                if (is_dir($moduleViews)) {
                    Q_Template::addPath($moduleViews);
                }

                $m = new $controller($moduleName);
                
                $moduleInfo = Doctrine::getTable('ModuleManager')->find($moduleName);
                if ($moduleInfo) {
                    Q_Template::addGlobal('currentModule', $moduleInfo->toArray());
                }

                //Q_Template::addGlobal('currentModule', $moduleName);

                return $m->$method($request);
            }
            
        }

        return self::error404();
    }
    
    /**
     * isAjaxRequest 
     * 
     * @static
     * @access public
     * @return boolean
     */
    static public function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }
    
    /**
     * isAjaxMethod 
     * 
     * @static
     * @access private
     * @param string $class 
     * @param string $method 
     * @return boolean
     */
    static private function isAjaxMethod($class, $method)
    {
        $methodReflection = self::getReflectionMethod($class, $method);

        return $methodReflection->hasAnnotation('Ajax');
    }
    
    /**
     * getAclAction 
     * 
     * @static
     * @access private
     * @param string $class 
     * @param string $method 
     * @return string
     */
    static private function getAclAction($class, $method)
    {
        $methodReflection = self::getReflectionMethod($class, $method);

        return ($methodReflection->hasAnnotation('AclAction'))
               ? $methodReflection->getAnnotation('AclAction')->value
               : '';
    }

    /**
     * getReflectionClass 
     * 
     * @param string $class 
     * @static
     * @access private
     * @return Q_ReflectionAnnotatedClass
     */
    static private function getReflectionClass($class)
    {
        if (!isset(self::$_reflection[$class])) {
            self::$_reflection[$class] = new Q_ReflectionAnnotatedClass($class);
        }

        return self::$_reflection[$class];
    }

    /**
     * getReflectionMethod 
     * 
     * @static
     * @access private
     * @param string $class 
     * @param string $method 
     * @return ReflectionMethod
     */
    static private function getReflectionMethod($class, $method)
    {
        $reflectionClass = self::getReflectionClass($class);

        return $reflectionClass->getMethod($method);
    }

    /**
     * error 404
     * 
     * @static
     * @access public
     * @param integer $en 
     * @return void
     */
    static public function error404()
    {
        include(WEB . DS . '404.php');
        exit();
    }
}