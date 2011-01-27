<?php
// константы
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'constants.php';

// Zend
//set_include_path(LIBS . DS);


// автозагрузка классов (из def CLASSES)
include CLASSES . DS . 'Autoloader.class.php';
Q_Autoloader::register();


// парсер Yaml
include_once LIBS . DS . 'Yaml' . DS . 'sfYaml.php';


// инициализация настроек
$settings = sfYaml::load(CONFIGS . DS . 'settings.yml');
if (isset($settings['time_zone'])) {
    date_default_timezone_set($settings['time_zone']);
}
if (isset($settings['language_code'])) {
    setlocale(LC_ALL, 'language_code');
}
// сессия
$time = time() + (integer)$settings['session_expire_time'] * 60;
session_set_cookie_params($time, '/');
session_start();
if (isset($_COOKIE[session_name()])) setcookie(session_name(), $_COOKIE[session_name()], $time, '/');

Q_Registry::set('settings', $settings);


// Doctrine ORM
require_once(LIBS . DS . 'Doctrine' . DS . 'Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));
Doctrine::setModelsDirectory(MODELS);
spl_autoload_register(array('Doctrine', 'modelsAutoload'));
$manager = Doctrine_Manager::getInstance();

$manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
$manager->setAttribute(Doctrine::ATTR_EXPORT, Doctrine::EXPORT_ALL);
$manager->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, true);

//$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);
$db = $settings['database'];

foreach ($db['connections'] as $connectionName => $value) {
    if ($connectionName == 'sqlite') {
        $value['dsn'] = preg_replace_callback('!%(.*)%!', create_function('$matches', 'return "/" . constant($matches[1]);'), $value['dsn']);
    }
    $conn = Doctrine_Manager::connection($value['dsn'], $connectionName);
    $conn->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, true);
    if ($value['collate']) $conn->setCollate($value['collate']);
    if ($value['charset']) $conn->setCharset($value['charset']);
}

$manager->setCurrentConnection($db['default']);


// шаблонизатор Twig
// http://www.twig-project.org/
include_once LIBS . DS . 'Twig' . DS . 'Autoloader.php';
Twig_Autoloader::register();


// debug
if (isset($settings['debug']) && $settings['debug']) {
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP')) {
        Q_Registry::set('FirePHP', true);
        //Doctrine::debug(true);
        Q_Debug::start();
    }
}


// обработка ошибок
set_error_handler('errorHandler');

// обработка исключений
set_exception_handler('exceptionHandler');

function errorHandler($code, $string, $file, $line)
{
    $exc = new Q_MyException($string, $code);
    $exc->setLine($line);
    $exc->setFile($file);
    throw $exc;
}

function exceptionHandler($exception, $message = null, $file = null, $line = null)
{
    header('HTTP/1.1 500 Internal Server Error');
    
    $toPrint = array(
        'error' => $exception->getMessage()
    );
    
    if (Q_Registry::get('settings', 'debug')) {
        $toPrint['file'] = $exception->getFile();
        $toPrint['line'] = $exception->getLine();
        $toPrint['trace'] = $exception->getTraceAsString();
    }
    
    if (Q_Router::isAjaxRequest()) {
        $toPrint['success'] = true;
        
        echo new Q_JsonResponse(
            $toPrint
        );
    } else {
        Q_Template::setAccess('admin');
        
        echo new Q_Response(
            'error.html',
            $toPrint
        );
    }
    
    exit();
}


// annotations
require_once LIBS . DS . 'Annotations' . DS . 'Annotations.php';
class AclAction extends Q_Annotation {};
class Ajax extends Q_Annotation {};