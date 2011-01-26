<?php
/**
 * Configs
 * получение/назначение конфигураций хранящихся в БД
 * 
 * @example
 * <code>
 * Q_Configs::set('News', 'defaultResultsPerPage', 10);
 * 
 * Q_Configs::get('News'); // return array
 * Q_Configs::get('News', 'defaultResultsPerPage'); // return value
 * </code>
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Configs
{
    
    /**
     * Кэш
     * @staticvar array
     */
    static private $_cache = array();
    
    
    /**
     * Getter
     *
     * @static
     * @access public
     * @param string|integer $key1
     * @param string|integer $key2
     * @return mixed 
     */
    static public function get($key1, $key2 = null)
    {
        
        if (is_null($key2) && isset(self::$_cache[$key1])) {
            return self::$_cache[$key1];
        } elseif (isset(self::$_cache[$key1][$key2])) {
            return self::$_cache[$key1][$key2];
        }
        
        $rawConfigs = Doctrine::getTable('Configs')->find($key1);
        
        $configs = array();
        foreach ($rawConfigs->value as $cfg) {
            foreach ($cfg as $k => $value) {
                $configs[$k] = $value;
            }
        }
        
        self::$_cache[$key1] = $configs;
        
        return (is_null($key2)) ? $configs : $configs[$key2];
    }
    
    
    /**
     * Setter
     *
     * @static
     * @access public
     * @param string $moduleName название модуля
     * @param string|integer $key ключ
     * @param mixed $value значение
     * @return void
     */
    static public function set($moduleName, $key, $value)
    {
        
    }

}