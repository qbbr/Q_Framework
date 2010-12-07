<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'bootstrap.php';

try {
    $html = Q_Router::parseRequest($_SERVER['REQUEST_URI']);
    
    // debug
    if (Q_Registry::get('FirePHP')) {
        Q_Debug::toFireBug();
    }

    echo $html;
} catch (Exception $exc) {
    if (Q_Registry::get('settings', 'debug')) {
        echo $exc->getMessage();
    }
}
