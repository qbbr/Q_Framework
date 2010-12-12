<?php
/**
 * Debug
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Debug
{
    /**
     * @staticvar boolean
     */
    static private $_started = false;

    /**
     * время старта
     * @staticvar float
     */
    static private $_startTime = 0;

    /**
     * @staticvar Doctrine_Connection_Profiler
     */
    static private $_profiler = null;


    /**
     * старт
     *
     * @static
     * @access public
     * @return void
     */
    static public function start()
    {
        self::$_startTime = microtime(true);

        self::$_profiler = new Doctrine_Connection_Profiler();

        Doctrine_Manager::connection()->addListener(self::$_profiler);

        // для mozilla
        include_once LIBS . DS . 'FirePHPCore' . DS . 'FirePHP.class.php';
        include_once LIBS . DS . 'FirePHPCore' . DS . 'fb.php';
        $console = FirePHP::getInstance(true);
        $console->registerErrorHandler(false);
        $console->registerExceptionHandler();
        $console->registerAssertionHandler(true, false);
        $console->log($_GET, '$_GET');
        $console->log($_POST, '$_POST');
        $console->log($_COOKIE, '$_COOKIE');
        if (isset($_SESSION)) $console->log($_SESSION, '$_SESSION');
        $console->log($_FILES, '$_FILES');
        $console->log($_SERVER, '$_SERVER');

        self::$_started = true;
    }


    /**
     * @static
     * @access public
     * @param mixed $var
     * @return boolean
     */
    static public function set($var)
    {
        if (self::$_started) {
            FB::log($var);
            return true;
        }

        return false;
    }


    /**
     * распечатать в FireBug (FirePHP)
     *
     * @static
     * @access public
     * @return mixed
     */
    static public function toFireBug()
    {
        if (!self::$_started) return false;

        $time = 0;
        $logs = array();

        foreach (self::$_profiler as $event) {
            $time += $event->getElapsedSecs();

            $log = array(
                'Action' => $event->getName(),
                'Time' => sprintf("%f", $event->getElapsedSecs())
            );

            $params = $event->getParams();
            if (!empty($params)) $log['Params'] = $params;

            $logs[$event->getQuery()][]= $log;
        }


        $i = 1;
        foreach ($logs as $query => $values) {
            $allTime = 0;
            $a = array(
                'SQL' => $query,
            );

            foreach ($values as $value) {
                $a[]=$value;
                $allTime += $value['Time'];
            }

            FB::send($a, $i . ') ' . $allTime);
            $i++;
        }

        FB::send($time, 'Total SQL query time');

        $allTime = microtime(true) - self::$_startTime;
        FB::send($allTime, 'Total load time');
    }


    /**
     * @static
     * @access public
     * @return boolean
     */
    static public function isStarted()
    {
        return self::$_started;
    }

}