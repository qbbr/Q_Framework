<?php
/**
 * базовый контроллер
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

abstract class Q_Controller
{

    /**
     * название модуля
     * @var string
     */
    protected $_moduleName = '';

    /**
     * директория модуля
     * @var string
     */
    protected $_moduleDir = '';

    /**
     * директория с шаблонами
     * @var string
     */
    protected $_viewsDir = '';

    /**
     * шаблонизатор
     * @var Twig_Environment
     */
    protected $_template = null;

    /**
     * client(by default) or admin
     * @var string
     */
    protected $_access = 'client'; // by default


    /**
     * конструктор
     * @param string $params параметры запроса (URI)
     */
//    public function __construct()
//    {
//    }


    /**
     * получить шаблонизатор
     *
     * @access protected
     * @return Twig_Environment
     */
    protected function getTemplate()
    {
        if (is_null($this->_template)) {
            $loader = new Twig_Loader_Filesystem($this->_viewsDir);

            $this->_template = new Twig_Environment($loader, array(
                'debug' => (bool)Q_Registry::get('settings', 'debug'),
                /*'cache' => TMP . DS . 'templates'*/
            ));
        }

        return $this->_template;
    }


    /**
     * перенаправление
     *
     * @access protected
     * @param string $url веб url
     * @return void
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit();
    }


    /**
     * вызвать контроллер
     *
     * @access protected
     * @param string $moduleName название контроллера
     * @param string $function название функции
     * @param array $args аргументы функции
     * @return mixed
     */
    protected function callController($moduleName, $function = null, $args = null)
    {
        $function = isset($function) ? $function : 'indexAction';

        $path = APPS . DS . $moduleName . DS . $this->_access . DS . $moduleName . '.' . $this->_access . 'Controller.php';

        echo $path;

        if (!is_file($path)) return false;

        include_once $path;

        $loadClass = new $moduleName($moduleName);
        if (is_callable(array($loadClass, $function))) {
            if (!isset($args)) $args = array();
            return call_user_func_array(array($loadClass, $function), $args);
        }

        return false;
    }

    /**
     * HTTP_GET_VARS
     *
     * @param string|integer $key ключ массива
     * @return mixed
     */
    protected function get($key)
    {
        return (isset($_GET[$key])) ? $_GET[$key] : null;
    }


    /**
     * HTTP_POST_VARS
     *
     * @param string|integer $key ключ массива
     * @return mixed
     */
    protected function post($key)
    {
        return (isset($_POST[$key])) ? $_POST[$key] : null;
    }


    /**
     * HTTP_COOKIE_VARS
     *
     * @param string|integer $key ключ массива
     * @return mixed
     */
    protected function cookie($key)
    {
        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : null;
    }


    /**
     * HTTP_SESSION_VARS
     *
     * @param string|integer $key ключ массива
     * @return mixed
     */
    protected function session($key)
    {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : null;
    }


    /**
     * HTTP_POST_FILES
     *
     * @param string|integer $key ключ массива
     * @return mixed
     */
    protected function files($key)
    {
        return (isset($_FILES[$key])) ? $_FILES[$key] : null;
    }

}