<?php
/**
 * регистр для хранения временных данных
 *
 * <code>
 * Registry::set('key', 'value');
 * Registry::get('key'); // return value
 * </code>
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Registry
{

    /**
     * регистр
     * @staticvar array
     */
    static private $_registry = array();


    /**
     * вставка данных в регистр
     *
     * @static
     * @param string $key ключ массива
     * @param mixed $value значение
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
     * получение данных из регистра
     *
     * @static
     * @param string $key1, $key2, $key3... ключи массива
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