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
if (isset($settings['timezone'])) {
    date_default_timezone_set($settings['timezone']);
}
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
//$dsn = sprintf('%s://%s:%s@%s/%s', $db['engine'], $db['user'], $db['password'], $db['host'], $db['name']);
//$dsn = 'dblib://qbbr:qbbr@192.168.100.103\qbbr';
$dsn = sprintf('%s://%s:%s@%s:%d/%s', $db['engine'], $db['user'], $db['password'], $db['host'], $db['port'], $db['name']);
$conn = Doctrine_Manager::connection($dsn);
$conn->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, true);
$conn->setCollate('utf8_unicode_ci');
$conn->setCharset('utf8');


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