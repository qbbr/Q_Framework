<?php
/**
 * Реестр для хранения временных данных
 *
 * @example
 * <code>
 * Registry::set('key', 'value');
 * Registry::get('key'); // return value
 * </code>
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */
class Q_Registry
{
    /**
     * 
     * @staticvar array Реестр
     */
    static private $_registry = array();

    /**
     * Вставка данных в реестр
     *
     * @static
     * @access public
     * @param string|integer $key Ключ массива
     * @param mixed $value Значение
     * @return boolean
     */
    static public function set($key, $value)
    {
        if (!isset(self::$_registry[$key])) {
            self::$_registry[$key] = $value;
            return true;
        }

        return false;
    }

    /**
     * Получение данных из реестра
     *
     * @static
     * @access public
     * @param string|integer $key1, $key2, $key3... Ключи массива
     * @return mixed
     */
    static public function get()
    {
        $args = func_get_args();

        $r = array();
        foreach ($args as $value) {
            if (empty($r) && isset(self::$_registry[$value])) {
                $r = self::$_registry[$value];
            } else if (isset($r[$value]) && is_array($r)) {
                $r = $r[$value];
            } else {
                return false;
            }
        }

        return $r;
    }
}