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
     * @var string client(by default) or admin
     */
    protected $_access = 'client';

    /**
     * Перенаправление
     *
     * @access protected
     * @param string $url Веб url
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
     * @param string $name Название контроллера
     * @return Q_Controller
     */
    protected function getController($name)
    {
        // banana
    }
    
    /**
     * HTTP_GET_VARS
     *
     * @access protected
     * @param string|integer $key Ключ массива
     * @return mixed
     */
    protected function get($key)
    {
        return (isset($_GET[$key])) ? $_GET[$key] : null;
    }

    /**
     * HTTP_POST_VARS
     *
     * @access protected
     * @param string|integer $key Ключ массива
     * @return mixed
     */
    protected function post($key)
    {
        return (isset($_POST[$key])) ? $_POST[$key] : null;
    }

    /**
     * HTTP_COOKIE_VARS
     *
     * @access protected
     * @param string|integer $key Ключ массива
     * @return mixed
     */
    protected function cookie($key)
    {
        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : null;
    }

    /**
     * HTTP_SESSION_VARS
     *
     * @access protected
     * @param string|integer $key Ключ массива
     * @return mixed
     */
    protected function session($key)
    {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : null;
    }

    /**
     * HTTP_POST_FILES
     *
     * @access protected
     * @param string|integer $key Ключ массива
     * @return mixed
     */
    protected function files($key)
    {
        return (isset($_FILES[$key])) ? $_FILES[$key] : null;
    }
}