<?php
/**
 * основные константы
 */

define('DS', DIRECTORY_SEPARATOR);

define('CONFIGS', dirname(__FILE__));
define('ROOT', dirname(CONFIGS));
define('CORE', ROOT . DS . 'core');
define('LIBS', CORE . DS . 'libs');
define('APPS', ROOT . DS . 'apps');
define('MODELS', APPS . DS . 'models');
define('CLIENT_CONTROLLERS', APPS . DS . 'client');
define('ADMIN_CONTROLLERS', APPS . DS . 'admin');
define('CLIENT_VIEWS', CLIENT_CONTROLLERS . DS . 'views');
define('ADMIN_VIEWS', ADMIN_CONTROLLERS . DS . 'views');

define('ZEND', LIBS . DS . 'Zend');
define('CLASSES', CORE . DS . 'classes');

define('WEB', ROOT . DS . 'web');
define('ETC', WEB . DS . 'etc');
define('CSS', ETC . DS . 'css');
define('IMG', ETC . DS . 'img');
define('UPLOADS', IMG . DS . 'uploads');
define('UPLOADS_URL', '/etc/img/uploads/'); // для браузера
define('JS', ETC . DS . 'js');

define('LOGS', ROOT . DS . 'logs');
define('TMP', ROOT . DS . 'tmp');
define('CACHE', TMP . DS . 'cache');

// соль
define('SALT', 'A(*&SD%A)(SD)(*#@()%&#)(@(*#@)#@^#!)alks');
define('SALT2', 'B8xR9E0s0w-bVibzrL0GD4u8OzFdiWMe6]');