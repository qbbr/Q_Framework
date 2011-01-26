<?php
/**
 * Передача flash сообщений по средствам сессии
 *
 * @example
 * <code>
 * // page1.php:
 * Q_Flash::set('alert', 'hello iksDi');
 * header('Location: /page2.php');
 *
 * // page2.php:
 * echo Q_Flash::get('alert'); // hello iksDi
 * </code>
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Flash
{

    /**
     * Кэш
     * @staticvar array
     */
    protected static $_cache = array();


    /**
     * Setter
     * 
     * @static
     * @access public
     * @param mixed $key 
     * @param mixed $value 
     * @return void
     */
    public static function set($key, $value)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['flash'][$key] = $value;
    }


    /**
     * Getter
     *
     * @static
     * @access public
     * @param mixed $key 
     * @return mixed
     */
    public static function get($key)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['flash'])) {
            self::$_cache = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }

        return (isset(self::$_cache[$key]))
               ? self::$_cache[$key]
               : null;
    }


    /**
     * Очистить 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function clear()
    {
        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }

        self::$_cache = array();
    }

}
