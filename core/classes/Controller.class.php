<?php
/**
 * Базовый контроллер
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

abstract class Q_Controller
{

    /**
     * название модуля
     * @var string
     */
    //protected $_moduleName = '';

    /**
     * директория модуля
     * @var string
     */
    //protected $_moduleDir = '';

    /**
     * директория с шаблонами
     * @var string
     */
    //protected $_viewsDir = '';

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
     * Перенаправление
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
     * Вызвать контроллер
     *
     * @access protected
     * @param string $moduleName название контроллера
     * @return Q_Controller
     */
    protected function getController($moduleName)
    {
        // banana
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
