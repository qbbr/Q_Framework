<?php
/**
 * Загрущик
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Autoloader
{

    /**
     * Регистрация
     * 
     * @static
     * @access public
     * @return void
     */
    static public function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self, 'classLoader'));
    }

    
    /**
     * Автозагрузка классов
     *
     * @static
     * @access public
     * @param string $className название класса
     * @return boolean
     */
    static public function classLoader($className)
    {
        if (0 !== strpos($className, 'Q_')) {
            return;
        }

        $class = CLASSES . DS . substr($className, 2) . '.class.php';
        
        
        if (isset($class) && is_file($class)) {
            include_once $class;
            return true;
        }

        return false;
    }

}