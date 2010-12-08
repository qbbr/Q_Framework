<?php
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'bootstrap.php';

require_once('Doctrine.php');

// Configure Doctrine Cli
// Normally these are arguments to the cli tasks but if they are set here the arguments will be auto-filled
$config = array(
    'data_fixtures_path'  =>  APPS . DS . 'data' . DS . 'fixtures.yml',
    'models_path'         =>  MODELS,
    'migrations_path'     =>  APPS . DS . 'data' . DS . 'migrations',
    'sql_path'            =>  APPS . DS . 'data' . DS . 'schema.sql',
    'yaml_schema_path'    =>  CONFIGS . DS . 'schema.yml',
    'generate_models_options' => array(
        'pearStyle' => true,
        'generateTableClasses' => true,
        'baseClassPrefix' => 'Base',
        'baseClassesDirectory' => null
    )
);

$cli = new Doctrine_Cli($config);
$cli->run($_SERVER['argv']);